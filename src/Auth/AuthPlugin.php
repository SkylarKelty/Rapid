<?php
/**
 * Rapid Prototyping Framework in PHP.
 * 
 * @author Skylar Kelty <skylarkelty@gmail.com>
 */

namespace Rapid\Auth;

abstract class AuthPlugin
{
	/**
	 * Login page hook.
	 * For compatibility with other Auth methods.
	 */
	public function login_hook($redirect) {
		
	}

	/**
	 * Logout page hook.
	 * For compatibility with other Auth methods.
	 */
	public function logout_hook($redirect) {
		
	}

	/**
	 * Can we register in this plugin?
	 */
	public function can_register() {
		return true;
	}

	/**
	 * Checks to see if we are logged in.
	 */
	public function logged_in() {
		global $USER;
		return $USER->id > 0;
	}
}