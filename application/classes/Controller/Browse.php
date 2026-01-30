<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Browse extends Controller
{

    // 配置虚拟目录映射
    private $virtualDirs = [
        'maven-public' => [
            'libs-release-local',
            'libs-snapshot-local',
            'plugins-release-local',
            'plugins-snapshot-local'
        ]
    ];

    public function action_index()
    {
        $fileParam = $this->request->param('file');

        // 判断是否为虚拟目录请求
        $realPath = $this->resolveVirtualDir($fileParam);
        if ($realPath) {
            $filename = realpath(REPOPATH . $realPath);
        } else {
            $filename = realpath(REPOPATH . $fileParam);
        }

        // 生成完整 URL
        $currenturl = URL::site('browse/' . $fileParam, TRUE);

        if (file_exists($filename)) {
            if (is_dir($filename)) {
                $dircontent = scandir($filename);

                $view = View::factory('browse/browser');
                $view->isRoot = strlen($fileParam) == 0;
                $view->currentpath = $this->endsWith($filename, '/') ? $filename : $filename . '/';
                $view->currenturl = $this->endsWith($currenturl, '/') ? $currenturl : $currenturl . '/';
                $view->dircontent = $dircontent;

                $this->response->body($view);
            } else {
                // 下载文件
                $this->response->send_file($filename);
            }
        } else {
            throw HTTP_Exception::factory(404, 'File not found!');
        }
    }

    // 解析虚拟目录，将其映射到实际的目录
    private function resolveVirtualDir($fileParam)
    {
        foreach ($this->virtualDirs as $virtualDir => $subDirs) {
            // 如果请求路径以虚拟目录开头，进行处理
            if (strpos($fileParam, $virtualDir . '/') === 0) {
                $relativePath = substr($fileParam, strlen($virtualDir) + 1); // 去掉虚拟目录部分
                foreach ($subDirs as $subDir) {
                    // 尝试将虚拟目录请求映射到实际的子目录
                    $realPath = REPOPATH . DIRECTORY_SEPARATOR . $subDir . DIRECTORY_SEPARATOR . $relativePath;
                    if (file_exists($realPath)) {
                        return $subDir . DIRECTORY_SEPARATOR . $relativePath; // 返回实际路径
                    }
                }
            }
        }
        return null; // 未找到匹配的虚拟目录映射
    }

    // 判断字符串是否以某个子串结尾
    function endsWith($haystack, $needle)
    {
        return $needle === "" || substr($haystack, -strlen($needle)) === $needle;
    }
}
