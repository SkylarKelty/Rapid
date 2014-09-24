<?php
/**
 * Rapid Prototyping Framework in PHP.
 * 
 * @author Skylar Kelty <skylarkelty@gmail.com>
 */

namespace Data;

/**
 * Basic model class within Database layer.
 */
abstract class DBModel extends Model
{
	private $table;

	/**
	 * Constructor.
	 */
	public function __construct($tablename) {
		parent::__construct();

		$this->table = $tablename;
	}

	/**
	 * Get table name
	 */
	public function get_table() {
		return $this->table;
	}

	/**
	 * Searches a database for all currently known fields
	 * and if we get a single hit, we fill out our other
	 * fields and return true.
	 */
	public function extrapolate() {
		global $DB;

		try {
			$obj = $DB->get_record($this->table, (array)$this->get_data(true));
			$this->bulk_set_data($obj, true);
			return true;
		} catch (\Exception $e) {
			return false;
		}
	}
}
