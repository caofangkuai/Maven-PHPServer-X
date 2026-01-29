<?php defined('SYSPATH') OR die('No direct script access.');

class Model_DBModel extends Model {
	
	// Database instance
	protected $_db;
	
	/**
	 * Loads the database.
	 *
	 *     $model = new Foo_Model($db);
	 *
	 * @param   mixed  $db  Database instance object or string
	 * @return  void
	 */
	public function __construct($db = NULL)
	{
		if ($db)
		{
			// Set the instance or name
			$this->_db = $db;
		}
		elseif ( ! $this->_db)
		{
			// Use the default name
			$this->_db = Database::$default;
		}

		if (is_string($this->_db))
		{
			// Load the database
			$this->_db = Database::instance($this->_db);
		}
	}
	
}
