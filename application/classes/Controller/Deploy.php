<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Deploy extends Controller_Gui
{

	private $repositories = array(
		'libs-release-local',
		'libs-snapshot-local',
		'plugins-release-local',
		'plugins-snapshot-local',
	);

	public function before()
	{
		parent::before();

		$this->page_title = '部署';
		$this->current_page = 'deploy';
	}

	public function action_index()
	{
		$this->script = '$( document ).ready(function() { $(\'input[type=file]\').bootstrapFileInput(); $(\'.file-inputs\').bootstrapFileInput(); });';
		$this->scripts = array('bootstrap.file-input.js');

		$this->template->content = View::factory('gui/deploy/main')->set('succes', Session::instance()->get_once('last_artifact_added'));
	}

	public function action_upload()
	{
		$array = Validation::factory($_FILES);
		$array->rule('artifact', 'Upload::not_empty');
		$array->rule('artifact', 'Upload::size', array(':value', '8M'));
		$array->rule('artifact', 'Upload::type', array(':value', array('jar')));
		$array->rule('artifact', 'Upload::valid');

		if ($array->check()) {
			// Upload is valid, save it
			$saved_file = Upload::save($array['artifact']);

			Session::instance()->set('artifact_file', $saved_file);
			Session::instance()->set('artifact_file_name', $_FILES['artifact']['name']);

			$this->redirect(Route::get('default')->uri(array('controller' => 'deploy', 'action' => 'pom')));
		} else {
			$this->script = '$( document ).ready(function() { $(\'input[type=file]\').bootstrapFileInput(); $(\'.file-inputs\').bootstrapFileInput(); });';
			$this->scripts = array('bootstrap.file-input.js');

			$errors = $array->errors('artifact');

			$this->template->content = View::factory('gui/deploy/main')->bind('errors', $errors);
		}
	}

	public function action_pom()
	{
		$saved_file = Session::instance()->get('artifact_file');
		if (!$saved_file || !file_exists($saved_file)) {
			$this->redirect(Route::get('default')->uri(array('controller' => 'deploy', 'action' => 'index')));
		}

		if ($this->request->method() == 'POST') {
			$settings = $_POST;
			$validation = Validation::factory($_POST);
			$validation->rule('groupId', 'not_empty');
			$validation->rule('artifactId', 'not_empty');
			$validation->rule('version', 'not_empty');
			$validation->rule('type', 'not_empty');
			$validation->rule(
				'type',
				function (Validation $array, $field, $value) {
					if (!in_array(strtolower($value), array('jar', 'war'))) {
						$array->error($field, 'Not Jar or War');
					}
				},
				array(':validation', ':field', ':value')
			);

			if ($validation->check()) {
				// Save artifact
				$path = REPOPATH . $settings['repository'] . '/' . str_replace('.', '/', $settings['groupId']) . '/' . $settings['artifactId'] . '/' . $settings['version'];
				if (!file_exists($path)) {
					mkdir($path, 0777, TRUE);
				}

				if (strlen($settings['classifier']) > 0) {
					$artifact_file = $path . '/' . $settings['artifactId'] . '-' . $settings['version'] . '-' . $settings['classifier'] . '.' . $settings['type'];

					rename($saved_file, $artifact_file);

					file_put_contents($artifact_file . '.sha1', sha1_file($artifact_file));
					file_put_contents($artifact_file . '.md5', md5_file($artifact_file));
				} else {
					$artifact_file = $path . '/' . $settings['artifactId'] . '-' . $settings['version'] . '.' . $settings['type'];
					$pom_file = $path . '/' . $settings['artifactId'] . '-' . $settings['version'] . '.pom';

					rename($saved_file, $artifact_file);
					file_put_contents($pom_file, $settings['pom']);

					file_put_contents($artifact_file . '.sha1', sha1_file($artifact_file));
					file_put_contents($pom_file . '.sha1', sha1_file($pom_file));
					file_put_contents($artifact_file . '.md5', md5_file($artifact_file));
					file_put_contents($pom_file . '.md5', md5_file($pom_file));
				}

				Session::instance()->set('last_artifact_added', $settings['artifactId']);
				Session::instance()->delete('artifact_file');
				Session::instance()->delete('artifact_file_name');

				$this->redirect(Route::get('default')->uri(array('controller' => 'deploy', 'action' => 'index')));
			} else {
				$errors = $validation->errors();

				$this->template->content = View::factory('gui/deploy/pom')->bind('settings', $settings)->bind('errors', $errors)->set('repositories', $this->repositories);
			}
		} else {
			$settings = $this->get_artifact_data_from_jar($saved_file);
			$settings['repository'] = 'libs-release-local';
			if (strlen($settings['pom']) == 0) {
				$generatedpom = array();
				$generatedpom['groupId'] = $settings['groupId'];
				$generatedpom['artifactId'] = $settings['artifactId'];
				$generatedpom['version'] = $settings['version'];
				$generatedpom['classifier'] = $settings['classifier'];
				$generatedpom['type'] = $settings['type'];
				$generatedpom['dependencies'] = array();
				Session::instance()->set('generatedpom', serialize($generatedpom));
			}
			$this->template->content = View::factory('gui/deploy/pom')->bind('settings', $settings)->set('repositories', $this->repositories);
		}
	}

	function action_dependency()
	{
		$saved_file = Session::instance()->get('artifact_file');
		if (!$saved_file || !file_exists($saved_file)) {
			$this->redirect(Route::get('default')->uri(array('controller' => 'deploy', 'action' => 'index')));
		}
		$generatedpom = unserialize(Session::instance()->get('generatedpom'));
		if ($this->request->method() == 'POST') {
			$dependency = $_POST;
			$validation = Validation::factory($_POST);
			$validation->rule('groupId', 'not_empty');
			$validation->rule('artifactId', 'not_empty');
			$validation->rule('version', 'not_empty');
			if ($validation->check()) {
				$generatedpom['dependencies'][] = $dependency;
				Session::instance()->set('generatedpom', serialize($generatedpom));

				$pom = $this->generatePom($generatedpom);
				$this->template->content = View::factory('gui/deploy/dependency')->bind('pom', $pom)->set('dependency', array());
			} else {
				$errors = $validation->errors();

				$pom = $this->generatePom($generatedpom);
				$this->template->content = View::factory('gui/deploy/dependency')->bind('pom', $pom)->bind('errors', $errors)->set('dependency', $this->dependency);
			}
		} else {
			$pom = $this->generatePom($generatedpom);
			$this->template->content = View::factory('gui/deploy/dependency')->bind('pom', $pom)->set('dependency', array());
		}
	}

	function get_artifact_data_from_jar($jarfile)
	{
		$zip = zip_open($jarfile);

		do {
			$zip_entry = zip_read($zip);
		} while ($zip_entry && !$this->endsWith(zip_entry_name($zip_entry), 'pom.xml'));

		if (! $zip_entry) {
			$settings = array();
			$artifact_file_name = Session::instance()->get('artifact_file_name');

			// 去掉扩展名（比如 .jar）
			$name_no_ext = preg_replace('/\.[^.]+$/', '', $artifact_file_name);

			// 用 - 分割
			$parts = preg_split('/-/', $name_no_ext);

			$settings['pom'] = '';

			if (count($parts) >= 3) {
				// 格式 a-b-1.0
				$settings['groupId'] = $parts[0];
				$settings['artifactId'] = $parts[1];
				$settings['version'] = $parts[2];
			} elseif (count($parts) == 2) {
				// 可能是 a-b 或 a-1.0
				if (preg_match('/^\d+(\.\d+)*$/', $parts[1])) {
					// 第二部分是版本号
					$settings['groupId'] = $parts[0];
					$settings['artifactId'] = $parts[0];
					$settings['version'] = $parts[1];
				} else {
					// 第二部分是 artifactId
					$settings['groupId'] = $parts[0];
					$settings['artifactId'] = $parts[1];
					$settings['version'] = '1.0';
				}
			} elseif (count($parts) == 1) {
				$settings['groupId'] = $parts[0];
				$settings['artifactId'] = $parts[0];
				$settings['version'] = '1.0';
			} else {
				// 极端情况
				$settings['groupId'] = 'unknown';
				$settings['artifactId'] = 'unknown';
				$settings['version'] = '1.0';
			}

			// type 自动取文件扩展名
			$settings['type'] = pathinfo($artifact_file_name, PATHINFO_EXTENSION) ?: 'jar';

			// classifier 留空
			$settings['classifier'] = '';

			if (strlen($settings['pom']) == 0) {
				$generatedpom = array(
					'groupId' => $settings['groupId'],
					'artifactId' => $settings['artifactId'],
					'version' => $settings['version'],
					'classifier' => $settings['classifier'],
					'type' => $settings['type'],
					'dependencies' => array()
				);

				// 生成完整 POM
				$settings['pom'] = $this->generatePom($generatedpom);

				Session::instance()->set('generatedpom', serialize($generatedpom));
			}

			return $settings;
		} else {
			zip_entry_open($zip, $zip_entry, 'r');

			$settings = array();

			$entry_content = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
			$settings['pom'] = $entry_content;

			$xml = new SimpleXMLElement($entry_content);
			// echo $xml->asXML();

			// get data from pom:
			$settings['groupId'] = $xml->groupId;
			if (strlen($settings['groupId']) == 0) {
				$settings['groupId'] = $xml->parent->groupId;
			}

			$settings['artifactId'] = $xml->artifactId;

			$settings['version'] = $xml->version;
			if (strlen($settings['version']) == 0) {
				$settings['version'] = $xml->parent->version;
			}

			$settings['type'] = 'jar';

			zip_entry_close($zip_entry);

			zip_close($zip);

			return $settings;
		}
	}

	function getSetting($pom, $setting)
	{

		// position of <em:version>
		$open_pos = strpos($pom, "<$setting>");
		if (!$open_pos) {
			return '';
		}

		// position of </em:version>
		$close_pos = strpos($pom, "</$setting>", $open_pos);

		// version
		return substr(
			$pom,
			$open_pos + strlen("<$setting>"),
			$close_pos - ($open_pos + strlen("<$setting>"))
		);
	}

	function endsWith($haystack, $needle)
	{
		return $needle === "" || substr($haystack, -strlen($needle)) === $needle;
	}

	function generatePom($generatedpom)
	{
		$pom = <<<CONTENT
<project xmlns="http://maven.apache.org/POM/4.0.0" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://maven.apache.org/POM/4.0.0 http://maven.apache.org/maven-v4_0_0.xsd">

	<modelVersion>4.0.0</modelVersion>
	<groupId>{$generatedpom['groupId']}</groupId>
	<artifactId>{$generatedpom['artifactId']}</artifactId>
	<packaging>{$generatedpom['type']}</packaging>
	<version>{$generatedpom['version']}</version>
	<!-- <classifier>{$generatedpom['classifier']}</classifier>
	<name>{$generatedpom['classifier']}</name> -->
	<description>PHP Maven Repository Uploaded</description>
CONTENT;

		if (count($generatedpom['dependencies']) > 0) {
			$pom .= "\r\n\r\n	<dependencies>";
			foreach ($generatedpom['dependencies'] as $dependency) {
				$pom .= "\r\n		<dependency>";
				$pom .= "\r\n			<groupId>{$dependency['groupId']}</groupId>";
				$pom .= "\r\n			<artifactId>{$dependency['artifactId']}</artifactId>";
				$pom .= "\r\n			<version>{$dependency['version']}</version>";
				if (isset($dependency['type']) && strlen($dependency['type']) > 0) $pom .= "\r\n			<packaging>{$dependency['type']}</packaging>";
				if (isset($dependency['classifier']) && strlen($dependency['classifier']) > 0) $pom .= "\r\n			<classifier>{$dependency['classifier']}</classifier>";
				$pom .= "\r\n		</dependency>";
			}
			$pom .= "\r\n	</dependencies>";
		}

		$pom .= "\r\n\r\n</project>";
		return $pom;
	}
} // End Welcome
