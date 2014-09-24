<?php
/**
 * Rapid Prototyping Framework in PHP.
 * 
 * @author Skylar Kelty <skylarkelty@gmail.com>
 */

namespace Auth;

class SimpleSAMLPHP
{
	private $saml;

	/**
	 * Constructor
	 */
	public function __construct() {
		require_once('simplesamlphp/lib/_autoload.php');
		$this->saml = new \SimpleSAML_Auth_Simple('default-sp');
	}

	/**
	 * Send us off to the SP.
	 */
	public function login($redirect = false) {
		global $USER, $PAGE;

		if (!$this->logged_in()) {
			$this->saml->requireAuth();
			return;
		}

        $attrs = $this->saml->getAttributes();

        $USER->username = $attrs['uid'][0];
        $USER->firstname = $attrs['givenName'][0];
        $USER->lastname = $attrs['sn'][0];
        $USER->email = $attrs['mail'][0];

        if ($redirect) {
        	$PAGE->redirect($redirect);
        }
	}

	/**
	 * Logout.
	 */
	public function logout($redirect = false) {
		global $USER, $SESSION, $PAGE;

		if ($this->logged_in()) {
			$this->saml->logout();
			return true;
		}

        $SESSION->regenerate();
        $USER = new User();

        if ($redirect) {
        	$PAGE->redirect($redirect);
        }
	}

	/**
	 * Checks to see if we are logged in.
	 */
	public function logged_in() {
		return $this->saml->isAuthenticated();
	}
}
