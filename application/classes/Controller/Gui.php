<?php defined('SYSPATH') or die('No direct script access.');

abstract class Controller_Gui extends Controller_Template {
	
	public $page_title;
	public $current_page;
	
	public $sub_menu = FALSE;
	public $current_sub_page;
	
	public $scripts = FALSE;
	public $script = FALSE;
	
	public $db;
	
	public function before()
	{
		parent::before();
		
		$this->db = Database::instance();
		
		View::bind_global('page_title', $this->page_title);
		View::bind_global('current_page', $this->current_page);
		View::bind_global('sub_menu', $this->sub_menu);
		View::bind_global('current_sub_page', $this->current_sub_page);
		View::bind_global('scripts', $this->scripts);
		View::bind_global('script', $this->script);
		
		$this->template->menu = View::factory('template/menu');
		$this->template->sub_menu = View::factory('template/submenu');
	}

} // End Controller GUI
