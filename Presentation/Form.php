<?php
/**
 * Rapid Prototyping Framework in PHP.
 * 
 * @author Skylar Kelty <skylarkelty@gmail.com>
 */

namespace Presentation;

/**
 * Basic form methods class.
 */
class Form
{
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
			$model = '\\Models\\' . $model;
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
			case \Data\Model::TYPE_INT:
				$formtype = 'number';
				break;

			case \Data\Model::TYPE_STRING:
				$formtype = 'text';
				break;

			case \Data\Model::TYPE_BOOL:
				$formtype = 'checkbox';
				break;

			case \Data\Model::TYPE_DECIMAL:
				$formtype = 'number';
				break;

			case \Data\Model::TYPE_TIMESTAMP:
				$formtype = 'datetime';
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
			'value' => $default
		);
	}

	/**
	 * Sets data of form fields.
	 */
	public function set_data($data) {
		foreach ($data as $k => $v) {
			$this->fields[$k]['value'] = $v;
		}
	}

	/**
	 * To string magic.
	 */
	public function __toString() {
		static $id = 0;

		$action = new \URL($this->action);
		$str = '<form action="' . $action . '" method="POST" role="form">';

		foreach ($this->fields as $k => $v) {
			$type = $v['type'];
			$value = $v['value'];
			$id = "frm" . $id++;
			$label = ucwords($k);

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
