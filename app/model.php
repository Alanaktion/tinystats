<?php

abstract class Model extends \DB\SQL\Mapper {

	protected $fields = array();

	function __construct($table_name = null) {
		$f3 = \Base::instance();

		if(empty($this->_table_name)) {
			if(empty($table_name)) {
				$f3->error(500, "Model instance does not have a table name specified.");
			} else {
				$this->table_name = $table_name;
			}
		}

		parent::__construct($f3->get("db.instance"), $this->_table_name, null, $f3->get("cache_expire.db"));
		return $this;
	}


	/**
	 * Set object created date if possible
	 * @return mixed
	 */
	function save() {
		if(array_key_exists("created_date", $this->fields) && !$this->query && !$this->get("created_date")) {
			$this->set("created_date", date("Y-m-d H:i:s"));
		}
		return parent::save();
	}

	/**
	 * Safely delete object if possible, if not, erase the record.
	 * @return mixed
	 */
	function delete() {
		if(array_key_exists("deleted_date", $this->fields)) {
			$this->deleted_date = date("Y-m-d H:i:s");
			return $this->save();
		} else {
			return $this->erase();
		}
	}

	/**
	 * Load by ID directly if a string is passed
	 * @param  string|array  $filter
	 * @param  array         $options
	 * @param  integer       $ttl
	 * @return mixed
	 */
	function load($filter=NULL, array $options=NULL, $ttl=0) {
		if(is_numeric($filter)) {
			return parent::load(array("id = ?", $filter), $options, $ttl);
		} else {
			return parent::load($filter, $options, $ttl);
		}
	}

	/**
	 * Takes two dates and creates an inclusive array of the dates between
	 * the from and to dates in YYYY-MM-DD format.
	 * @param  string $strDateFrom
	 * @param  string $strDateTo
	 * @return array
	 */
	protected function _createDateRangeArray($dateFrom, $dateTo) {
		$range = array();

		$from = strtotime($dateFrom);
		$to = strtotime($dateTo);

		if ($to >= $from) {
			$range[] = date('Y-m-d', $from); // first entry
			while ($from < $to) {
				$from += 86400; // add 24 hours
				$range[] = date('Y-m-d', $from);
			}
		}

		return $range;
	}

	/**
	 * Get most recent value of field
	 * @param  string $key
	 * @return mixed
	 */
	protected function _getPrev($key) {
		if(!$this->query) {
			return null;
		}
		$prev_fields = $this->query[count($this->query) - 1]->fields;
		return array_key_exists($key, $prev_fields) ? $prev_fields[$key]["value"] : null;
	}

}
