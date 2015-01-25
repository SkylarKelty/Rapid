<?php
/**
 * Rapid Prototyping Framework in PHP.
 * 
 * @author Skylar Kelty <skylarkelty@gmail.com>
 */

namespace Rapid\Auth;

class SimpleSAMLPHP extends AuthPlugin
{
	private $_saml;

	/**
	 * Constructor
	 */
	public function __construct() {
		require_once('simplesamlphp/lib/_autoload.php');
		$this->_saml = new \SimpleSAML_Auth_Simple('default-sp');
	}

	/**
	 * Login page hook.
	 * For compatibility with other Auth methods.
	 */
	public function login_hook($redirect) {
		global $PAGE;

		if (!$this->logged_in()) {
			$this->_saml->requireAuth();
			return;
		}

        $attrs = $this->_saml->getAttributes();
        $this->setup_user($attrs);

        $PAGE->redirect($redirect);
	}

	/**
	 * Setup the user.
	 */
	protected function setup_user($attrs) {
		global $USER;

        $USER->username = $attrs['uid'][0];
        $USER->firstname = $attrs['givenName'][0];
        $USER->lastname = $attrs['sn'][0];
        $USER->email = $attrs['mail'][0];
	}

	/**
	 * Logout page hook.
	 * For compatibility with other Auth methods.
	 */
	public function logout_hook($redirect) {
		global $USER, $SESSION, $PAGE;

		if ($this->logged_in()) {
			$this->_saml->logout();
			return true;
		}

        $SESSION->regenerate();
        $USER = new User();

    	$PAGE->redirect($redirect);
	}

	/**
	 * Can we register in this plugin?
	 */
	public function can_register() {
		return false;
	}

	/**
	 * Checks to see if we are logged in.
	 */
	public function logged_in() {
		return $this->_saml->isAuthenticated();
	}
}
