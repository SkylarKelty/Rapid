<?php
/**
 * Rapid Prototyping Framework in PHP.
 * 
 * @author Skylar Kelty <skylarkelty@gmail.com>
 */

namespace Data;

/**
 * Basic model class.
 */
abstract class Model
{
	const TYPE_INT = 1;
	const TYPE_STRING = 2;
	const TYPE_BOOL = 4;
	const TYPE_DECIMAL = 8;
	const TYPE_TIMESTAMP = 16;

	private $fields;
	private $data;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->data = array();
		$this->fields = array();
	}

	/**
	 * Define a valid field.
	 */
	protected function add_field($name, $type, $length = 0, $hidden = false, $locked = false) {
		$this->fields[$name] = array(
			'type' => $type,
			'length' => $length,
			'hidden' => $hidden,
			'locked' => $locked
		);
	}

	/**
	 * Get the default value for a field type.
	 */
	private function get_default($type) {
		switch ($type) {
			case Model::TYPE_INT:
				return 0;

			case Model::TYPE_STRING:
				return '';

			case Model::TYPE_BOOL:
				return false;

			case Model::TYPE_DECIMAL:
				return 0.0;

			case Model::TYPE_TIMESTAMP:
				return '1970-01-01 00:00:00';

			default:
				throw new \Exception("Invalid data type '$type'!");
		}
	}

	/**
	 * Validate value for a field type.
	 */
	private function validate($type, $value) {
		switch ($type) {
			case Model::TYPE_INT:
				return is_int($value) || preg_match('/^\d*$/', $value);

			case Model::TYPE_STRING:
				return is_string($value);

			case Model::TYPE_BOOL:
				return $value === true || $value === false;

			case Model::TYPE_DECIMAL:
				return is_numeric($value) || preg_match('/^(.\d)*$/', $value);

			case Model::TYPE_TIMESTAMP:
				return preg_match('/^\d\d\d\d-(\d)?\d-(\d)?\d \d\d:\d\d:\d\d$/', $value);

			default:
				throw new \Exception("Invalid data type '$type'!");
		}
	}

	/**
	 * Magic set.
	 */
	public function __set($name, $value) {
		if (!isset($this->fields[$name])) {
			throw new \Exception("Invalid field name '$name'!");
		}

		$field = $this->fields[$name];

		if (!$this->validate($field['type'], $value)) {
			throw new \Exception("Invalid value for field '$name'!");
		}
		
		if ($field['locked']) {
			throw new \Exception("Attempted to modify locked field '$name'!");
		}

		$this->data[$name] = $value;
	}

	/**
	 * Magic get.
	 */
	public function __get($name) {
		if (isset($this->data[$name])) {
			return $this->data[$name];
		}

		$field = $this->fields[$name];
		return $this->get_default($field['type']);
	}

	/**
	 * Magic isset.
	 */
	public function __isset($name) {
		return isset($this->fields[$name]);
	}

	/**
	 * Magic unset.
	 */
	public function __unset($name) {
		if (isset($this->data[$name])) {
			unset($this->data[$name]);
		}
	}

	/**
	 * Returns data on the fields.
	 */
	public function get_fields() {
		return (object)$this->fields;
	}

	/**
	 * Returns the data contained in this model as a stdClass.
	 */
	public function get_data($includehidden = false) {
		if ($includehidden) {
			return (object)$this->data;
		}

		$result = array();
		foreach ($this->fields as $k => $v) {
			if ($v['hidden']) {
				continue;
			}

			$result[$k] = $this->$k;
		}

		return $result;
	}

	/**
	 * Convert an array to a model instance.
	 */
	public function bulk_set_data($array, $force = false) {
		if (!is_array($array)) {
			$array = (array)$array;
		}

		foreach ($array as $key => $value) {
			if ($force) {
				$this->data[$key] = $value;
			} else {
				$this->$key = $value;
			}
		}
	}
}
