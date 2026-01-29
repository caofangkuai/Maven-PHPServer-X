<?php defined('SYSPATH') OR die('No direct script access.');

class Model_Artifact extends Model_DBModel {
	
	public function getDb() {
		return $this->_db;
	}
	
}
