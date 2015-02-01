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
	const TYPE_EMAIL = 64;
	const TYPE_TEXT = 128;

	const RULE_REQUIRED = 1;
	const RULE_MIN_LENGTH = 2;
	const RULE_MAX_LENGTH = 4;

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
	public function add_element($name, $type, $default = '', $label = '') {
		$element = 'input';
		$formtype = 'text';

		switch ($type) {
			case static::TYPE_INT:
				$formtype = 'number';
				break;

			case static::TYPE_STRING:
				$formtype = 'text';
				break;

			case static::TYPE_TEXT:
				$element = 'textarea';
				$formtype = '';
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

			case static::TYPE_EMAIL:
				$formtype = 'email';
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
			'label' => empty($label) ? $name : $label,
			'submitted' => false,
			'rules' => array(),
			'errors' => array()
		);

		if (isset($_REQUEST[$name])) {
			$this->set_field($name, trim($_REQUEST[$name]));
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
				$this->validate($k);
			}
		}

		// Incomplete?
		if (count($return) !== count($this->fields)) {
			return null;
		}

		return $return;
	}

	/**
	 * Rules.
	 */
	public function add_rule($field, $rule, $value = '') {
		$this->fields[$field]['rules'][$rule] = $value;
	}

	/**
	 * Validate field.
	 */
	public function add_error($field, $error) {
		$this->fields[$field]['errors'][] = $error;
	}

	/**
	 * Validate field.
	 */
	private function validate($name) {
		$field = $this->fields[$name];
		if (empty($field['rules'])) {
			return true;
		}

		$rules = $field['rules'];
		foreach ($rules as $rule => $value) {
			switch ($rule) {
				case static::RULE_REQUIRED:
					if (empty($field['value'])) {
						$this->add_error($name, "cannot be blank!");
						return false;
					}
				break;

				case static::RULE_MIN_LENGTH:
					if (strlen($field['value']) < $value) {
						$this->add_error($name, "must be longer than {$value} characters.");
						return false;
					}
				break;

				case static::RULE_MAX_LENGTH:
					if (strlen($field['value']) > $value) {
						$this->add_error($field, "must be shorter than {$value} characters.");
						return false;
					}
				break;
			}
		}

		return true;
	}

	/**
	 * Returns true if the form has any errors.
	 */
	public function has_errors() {
		foreach ($this->fields as $k => $v) {
			if (!empty($v['errors'])) {
				return true;
			}
		}
		return false;
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
			$label = $OUTPUT->escape_string(ucwords($v['label']));

			$class = 'form-group';
			if (!empty($v['errors'])) {
				$class .= ' has-error';
			}

			$str .= "<div class=\"{$class}\">";

			switch ($v['element']) {
				case 'input':
					$str .= "<label for=\"{$id}\">{$label}</label>";
					$str .= "<input name=\"{$k}\" type=\"{$type}\" value=\"{$value}\" class=\"form-control\" />";
					break;
				case 'textarea':
					$str .= "<label for=\"{$id}\">{$label}</label>";
					$str .= "<textarea name=\"{$k}\" class=\"form-control\" rows=\"4\">{$value}</textarea>";
					break;
			}

			if (!empty($v['errors'])) {
				foreach ($v['errors'] as $error) {
					$str .= $label . ' ' . $error;
				}
			}

			$str .= '</div>';
		}

		$str .= '<button type="submit" class="btn btn-default">Submit</button>';
		$str .= '</form>';

		return $str;
	}
}
