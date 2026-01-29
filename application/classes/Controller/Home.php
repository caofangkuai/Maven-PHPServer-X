<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Home extends Controller_Gui {
	
	public function before()
	{
		parent::before();
		
		$this->sub_menu = array(
			'index' => '主页',
			'maven' => 'Maven设置'
		);
		
		$this->current_page = 'home';
	}

	public function action_index()
	{
		$this->page_title = '主页';
		$this->current_sub_page = 'index';
		
		$this->template->content = View::factory('gui/home/main');
	}
	
	public function action_maven()
	{
		$this->page_title = 'Maven';
		$this->current_sub_page = 'maven';
		
		$this->template->content = View::factory('gui/home/maven');
	}

} // End Welcome
