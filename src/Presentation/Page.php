<?php
/**
 * Rapid Prototyping Framework in PHP.
 * 
 * @author Skylar Kelty <skylarkelty@gmail.com>
 */

namespace Rapid\Presentation;

/**
 * Basic page methods class.
 */
class Page
{
	private $navigation;
	private $stylesheets;
	private $scripts;
	private $title;
	private $url;
	private $notifications;

	/**
	 * Constructor.
	 */
	public function __construct() {
		global $CFG;

		$this->title = '';
		$this->url = '/';
		$this->notifications = array();
		$this->stylesheets = array();
		$this->scripts = array();

		// Build basic nav structure.
		$this->navigation = array(
			'Home' => '/'
		);
	}

	/**
	 * Returns true if the given url is the current page.
	 */
	public function is_active($url) {
		return $url == $this->url;
	}

	/**
	 * Returns pages in the nav bar.
	 */
	public function get_navbar() {
		return $this->navigation;
	}

	/**
	 * Add a page to the navbar.
	 */
	public function add_menu_item($name, $href) {
		$this->navigation[$name] = $href;
	}

	/**
	 * Remove a page from the navbar.
	 */
	public function remove_menu_item($name) {
		unset($this->navigation[$name]);
	}

	/**
	 * Setup navigation bar.
	 */
	public function menu($array) {
		$this->navigation = $array;
	}

	/**
	 * Scans media dirs for stylesheets.
	 */
	private function scan_stylesheets() {
		global $CFG;

		$cssdir = substr($CFG->cssroot, strlen($CFG->dirroot) + 1);
		$list = scandir($CFG->cssroot);
		foreach ($list as $filename) {
			if (strpos($filename, '.css') !== false) {
				$this->require_css($cssdir . '/' . $filename);
			}
		}
	}

	/**
	 * Returns all stylesheets.
	 */
	public function get_stylesheets() {
		global $CFG;

		$this->scan_stylesheets();

		$sheets = array_unique($this->stylesheets);
		$str = '';
		foreach ($sheets as $sheet) {
			$str .= "<link href=\"$sheet\" rel=\"stylesheet\">\n";
		}
		return $str;
	}

	/**
	 * Scans media dirs for javascript.
	 */
	private function scan_javascripts() {
		global $CFG;

		$jsdir = substr($CFG->jsroot, strlen($CFG->dirroot) + 1);
		$list = scandir($CFG->jsroot);
		foreach ($list as $filename) {
			if (strpos($filename, '.js') !== false) {
				$this->require_js($jsdir . '/' . $filename);
			}
		}
	}

	/**
	 * Returns all javascript.
	 */
	public function get_javascript() {
		global $CFG;

		$this->scan_javascripts();

		$scripts = array_unique($this->scripts);
		$str = '';
		foreach ($scripts as $script) {
			$str .= "<script src=\"$script\"></script>\n";
		}
		return $str;
	}

	/**
	 * Adds a Javascript to the page.
	 */
	public function require_js($path) {
		$url = new \Rapid\URL($path);
		$this->scripts[] = $url->out();
	}

	/**
	 * Adds a Stylesheet to the page.
	 */
	public function require_css($path) {
		$url = new \Rapid\URL($path);
		$this->stylesheets[] = $url->out();
	}

	/**
	 * Set the page title.
	 */
	public function set_title($title) {
		$this->title = $title;
	}

	/**
	 * Get the page title.
	 */
	public function get_title() {
		return $this->title;
	}

	/**
	 * Set the page url.
	 */
	public function set_url($url) {
		$this->url = $url;
	}

	/**
	 * Get the page url relative to wwwroot.
	 */
	public function get_url() {
		return $this->url;
	}

	/**
	 * Redirect somewhere.
	 */
	public function redirect($url) {
		if (!is_object($url)) {
			$url = new \Rapid\URL($url);
		}

		header('Location: ' . $url);
		die("Redirecting you to '$url'...");
	}

	/**
	 * Notify the user about something.
	 */
	public function notify($message) {
		if ($this->notifications === null) {
			echo $message;
			return;
		}

		$this->notifications[] = $message;
	}

	/**
	 * Returns all notifications.
	 */
	public function get_notifications() {
		$notifications = $this->notifications;
		$this->notifications = null;
		return $notifications;
	}

	/**
	 * Require and return a specific request parameter.
	 */
	public function require_param($name) {
		global $OUTPUT;

		if (!isset($_REQUEST[$name])) {
			$OUTPUT->error_page("Required parameter not found: '{$name}'.");
		}

		return $_REQUEST[$name];
	}

	/**
	 * Return a specific request parameter.
	 */
	public function optional_param($name, $default = null) {
		if (!isset($_REQUEST[$name])) {
			return $default;
		}

		return $_REQUEST[$name];
	}
}
