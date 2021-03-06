<?php
/**
 * Rapid Prototyping Framework in PHP.
 * 
 * @author Skylar Kelty <skylarkelty@gmail.com>
 */

namespace Rapid\Auth;

class User
{
	private $user;

	/**
	 * Constructor.
	 */
	public function __construct() {
		global $SESSION;

		if (isset($SESSION->_user)) {
			$this->user = $SESSION->_user;
		} else {
			$this->reset();
		}
	}

	/**
	 * Reset USER (logout).
	 */
	public function reset() {
		global $SESSION;

		$this->user = array(
			'id' => 0,
			'username' => 'guest',
			'firstname' => 'Guest',
			'lastname' => 'User',
			'email' => ''
		);

		unset($SESSION->_user);
	}

	/**
	 * Magic Sets.
	 */
	public function __set($name, $val) {
		global $SESSION;

		$this->user[$name] = $val;
		$SESSION->_user = $this->user;
	}

	/**
	 * Magic Gets.
	 */
	public function __get($name) {
		return $this->user[$name];
	}

	/**
	 * Magic isset.
	 */
	public function __isset($name) {
		return isset($this->user[$name]);
	}

	/**
	 * Magic unset.
	 */
	public function __unset($name) {
		global $SESSION;

		unset($this->user[$name]);
		$SESSION->_user = $this->user;
	}

	/**
	 * Login hook.
	 */
	public function on_login() {
		// Do nothing.
	}

	/**
	 * Are we logged in?
	 */
	public function loggedin() {
		return $this->user['username'] != 'guest';
	}
}
