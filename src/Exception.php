<?php
/**
 * Rapid Prototyping Framework in PHP.
 * 
 * @author Skylar Kelty <skylarkelty@gmail.com>
 */

namespace Rapid;

/**
 * Rapid's Exception Class.
 */
class Exception extends \Exception
{
	/** Extra data associated with the exception. */
	public $extra;

	/** Extra debuginfo associated with the exception. */
	public $debuginfo;

	/**
	 * Constructor.
	 */
	public function __construct($message, $extra = array(), $code = 0, $previous = null) {
		$this->extra = $extra;
		$this->debuginfo = debug_backtrace();

		parent::__construct($message, $code, $previous);
	}

	/**
	 * Override __toString.
	 */
	public function __toString() {
		$message = '<h3>Message</h3>' . $this->getMessage();

		$table = new \Rapid\Presentation\Table(array(
			'File', 'Call', 'Object'
		));

		foreach ($this->debuginfo as $k => $v) {
			if (isset($v['class']) && $v['class'] == 'Rapid\Exception') {
				continue;
			}

			$table->add_row($this->get_debug_row($v));
		}

		$message .= '</br><h3>Backtrace</h3>' . $table;
		return $message;
	}

	/**
	 * Turns one entry in a debug trace into a prettier array.
	 */
	private function get_debug_row($entry) {
		$file = $entry['file'];
		$line = $entry['line'];

		$function = $entry['function'];
		$args = join(', ', $entry['args']);
		$function = "{$function}({$args});";

		if (isset($entry['class'])) {
			$class = $entry['class'];
			$function = $class . $entry['type'] . $function;
		}

		$array = array(
			'file' => $file . " (Line: {$line})",
			'call' => $function,
			'object' => ''
		);

		if (isset($entry['object'])) {
			$array['object'] = serialize($entry['object']);
		}

		return $array;
	}
}
