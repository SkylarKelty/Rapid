<?php
/**
 * Rapid Prototyping Framework in PHP.
 * 
 * @author Skylar Kelty <skylarkelty@gmail.com>
 */

namespace Rapid\Presentation;

/**
 * Basic form methods class.
 */
class Form
{
	const TYPE_INT = 1;
	const TYPE_STRING = 2;
	const TYPE_BOOL = 4;
	const TYPE_DECIMAL = 8;
	const TYPE_TIMESTAMP = 16;
	const TYPE_PASSWORD = 32;

	private $action;
	private $fields;

	/**
	 * Simple Constructor.
	 */
	public function __construct($action) {
		$this->action = $action;
		$this->fields = array();
	}

	/**
	 * Generate a form based on a model.
	 */
	public function import_model($model) {
		if (!is_object($model)) {
			$model = new $model();
		}

		$fields = (array)$model->get_fields(true);
		foreach ($fields as $k => $v) {
			$type = $v['type'];
			if ($v['hidden']) {
				$type = 'hidden';
			}

			$this->add_element($k, $type);
		}
	}

	/**
	 * Add an element.
	 */
	public function add_element($name, $type, $default = '') {
		$element = 'input';
		$formtype = 'text';

		switch ($type) {
			case static::TYPE_INT:
				$formtype = 'number';
				break;

			case static::TYPE_STRING:
				$formtype = 'text';
				break;

			case static::TYPE_BOOL:
				$formtype = 'checkbox';
				break;

			case static::TYPE_DECIMAL:
				$formtype = 'number';
				break;

			case static::TYPE_TIMESTAMP:
				$formtype = 'datetime';
				break;

			case static::TYPE_PASSWORD:
				$formtype = 'password';
				break;

			case 'hidden':
				$formtype = 'hidden';
				break;

			default:
				throw new \Exception("Invalid form type '$type'!");
		}

		$this->fields[$name] = array(
			'element' => $element,
			'type' => $formtype,
			'value' => $default,
			'submitted' => false
		);

		if (isset($_REQUEST[$name])) {
			$this->set_field($name, $_REQUEST[$name]);
			$this->fields[$name]['submitted'] = true;
		}
	}

	/**
	 * Sets value of form field.
	 */
	public function set_field($field, $value) {
		$this->fields[$field]['value'] = $value;
	}

	/**
	 * Sets data of form fields.
	 */
	public function set_data($data) {
		foreach ($data as $k => $v) {
			$this->set_field($k, $v);
		}
	}

	/**
	 * Returns data of submitted form fields.
	 */
	public function get_data() {
		$return = array();
		foreach ($this->fields as $k => $v) {
			if ($v['submitted']) {
				$return[$k] = $v['value'];
			}
		}

		// Incomplete?
		if (count($return) !== count($this->fields)) {
			return null;
		}

		return $return;
	}

	/**
	 * To string magic.
	 */
	public function __toString() {
		global $OUTPUT;

		static $id = 0;

		$action = new \Rapid\URL($this->action);
		$str = '<form action="' . $action . '" method="POST" role="form">';

		foreach ($this->fields as $k => $v) {
			$type = $v['type'];
			$value = $OUTPUT->escape_string($v['value']);
			$id = "frm" . $id++;
			$label = $OUTPUT->escape_string(ucwords($k));

			$str .= '<div class="form-group">';

			switch ($v['element']) {
				case 'input':
					$str .= "<label for=\"{$id}\">{$label}</label>";
					$str .= "<input name=\"{$k}\" type=\"{$type}\" value=\"{$value}\" class=\"form-control\" />";
					break;
			}

			$str .= '</div>';
		}

		$str .= '<button type="submit" class="btn btn-default">Submit</button>';
		$str .= '</form>';

		return $str;
	}
}
