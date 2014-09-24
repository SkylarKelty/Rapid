<?php
/**
 * Rapid Prototyping Framework in PHP.
 * 
 * @author Skylar Kelty <skylarkelty@gmail.com>
 */

namespace Auth;

class Session
{
	public function __construct() {
		@session_start();
	}

	/**
	 * Magic Sets.
	 */
	public function __set($name, $val) {
		$_SESSION[$name] = $val;
	}

	/**
	 * Magic Gets.
	 */
	public function __get($name) {
		return $_SESSION[$name];
	}

	/**
	 * Magic isset.
	 */
	public function __isset($name) {
		return isset($_SESSION[$name]);
	}

	/**
	 * Magic unset.
	 */
	public function __unset($name) {
		if (isset($_SESSION[$name])) {
			unset($_SESSION[$name]);
		}
	}

	/**
	 * Regenerate the session.
	 */
	public function regenerate() {
		session_destroy();
		@session_start();
	}
}
