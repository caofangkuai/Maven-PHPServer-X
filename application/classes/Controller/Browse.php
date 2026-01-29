<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Browse extends Controller {

    public function action_index()
    {
        $fileParam = $this->request->param('file');
        $filename = realpath(REPOPATH . $fileParam);

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

    // 判断字符串是否以某个子串结尾
    function endsWith($haystack, $needle)
    {
        return $needle === "" || substr($haystack, -strlen($needle)) === $needle;
    }
}
