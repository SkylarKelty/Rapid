<?php
/**
 * Rapid Prototyping Framework in PHP.
 * 
 * @author Skylar Kelty <skylarkelty@gmail.com>
 */

namespace Presentation;

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

	/**
	 * Constructor.
	 */
	public function __construct() {
		global $CFG;

		$this->title = '';
		$this->url = '/';

		// Build a list of stylesheets.
		$this->stylesheets = array();
		$cssdir = substr($CFG->cssroot, strlen($CFG->dirroot) + 1);
		$list = scandir($CFG->cssroot);
		foreach ($list as $filename) {
			if (strpos($filename, '.css') !== false) {
				$this->require_css($cssdir . '/' . $filename);
			}
		}

		// Build a list of scripts.
		$this->scripts = array();
		$jsdir = substr($CFG->jsroot, strlen($CFG->dirroot) + 1);
		$list = scandir($CFG->jsroot);
		foreach ($list as $filename) {
			if (strpos($filename, '.js') !== false) {
				$this->require_js($jsdir . '/' . $filename);
			}
		}

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
	 * Returns all stylesheets.
	 */
	public function get_stylesheets() {
		global $CFG;

		$sheets = array_unique($this->stylesheets);
		$str = '';
		foreach ($sheets as $sheet) {
			$str .= "<link href=\"$sheet\" rel=\"stylesheet\">\n";
		}
		return $str;
	}

	/**
	 * Returns all javascript.
	 */
	public function get_javascript() {
		global $CFG;

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
		$url = new \URL($path);
		$this->scripts[] = $url->out();
	}

	/**
	 * Adds a Stylesheet to the page.
	 */
	public function require_css($path) {
		$url = new \URL($path);
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
			$url = new \URL($url);
		}

		header('Location: ' . $url);
		die("Redirecting you to '$url'...");
	}
}
