<?php
/**
 * Rapid Prototyping Framework in PHP.
 * 
 * @author Skylar Kelty <skylarkelty@gmail.com>
 */

/**
 * URL Class.
 */
class URL
{
	private $parameters;
	private $url;

	/**
	 * Creates a URL that will always be joined to wwwroot.
	 */
	public function __construct($base = '') {
		global $CFG;

		$this->parameters = array();

		if ($base == '') {
			$this->url = $CFG->wwwroot;
			return;
		}

		// Strip wwwroot out.
		if (strpos($base, $CFG->wwwroot) === 0) {
			$base = substr($base, strlen($CFG->wwwroot) + 1);
		}

		// Take out parameters.
		list($base, $parameters) = static::extract_parameters($base);
		$this->parameters = $parameters;

		if (strpos($base, 'http') !== 0 && strpos($base, '//') !== 0) {
			$base = ltrim($base, '/');
			$base = $CFG->wwwroot . '/' . $base;
		}
	
		$base = rtrim($base, '/');
		$this->url = $base;
	}

	/**
	 * Extract parameters from URL.
	 */
	public static function extract_parameters($url) {
		if (strpos($url, '?') === false) {
			return array($url, array());
		}

		$final = array();

		list($url, $parameters) = explode('?', $url, 2);
		$parameters = explode('&', $parameters);
		foreach ($parameters as $param) {
			list($name, $val) = explode('=', $param, 2);
			$name = urldecode($name);
			$val = urldecode($val);
			$final[$name] = $val;
		}

		return array($url, $final);
	}

	/**
	 * Join a URL onto this one.
	 */
	public function join($url) {
		// Take out parameters.
		list($url, $parameters) = static::extract_parameters($url);
		$this->parameters = array_merge($this->parameters, $parameters);

		$url = trim($url, '/');
		$this->url .= '/' . $url;
	}

	/**
	 * Add parameters.
	 */
	public function set_param($name, $val) {
		$this->parameters[$name] = $val;
	}

	/**
	 * Get parameters from URL.
	 */
	public function get_param($name) {
		return isset($this->parameters[$name]) ? $this->parameters[$name] : null;
	}

	/**
	 * Returns the URL as a string.
	 */
	public function out() {
		$url = $this->url;
		if (!empty($this->parameters)) {
			$parameters = array();
			foreach ($this->parameters as $k => $v) {
				$parameters[] = url_encode($k) . '=' . url_encode($v);
			}

			$url = '?' . implode('&', $parameters);
		}

		return $url;
	}

	/**
	 * Magic to string
	 */
	public function __toString() {
		return $this->out();
	}
}