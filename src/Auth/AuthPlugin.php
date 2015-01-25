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
	 *
	 * @param string $redirect Where to go if we are logged in.
	 */
	public function login_page_hook($redirect) {
		// Dont do anything special.
	}

	/**
	 * Logout page hook.
	 * For compatibility with other Auth methods.
	 */
	public function logout($redirect) {
		global $USER, $SESSION, $PAGE;

		$USER->reset();

    	$PAGE->redirect($redirect);
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