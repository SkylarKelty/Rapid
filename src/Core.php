<?php
/**
 * Rapid Prototyping Framework in PHP.
 * 
 * @author Skylar Kelty <skylarkelty@gmail.com>
 */

namespace Rapid;

/**
 * Rapid's Core Class.
 */
class Core
{
	/**
	 * Initialize Rapid Framework.
	 */
	public static function init() {
		global $CFG, $OUTPUT;

		if (!defined('CLI_SCRIPT')) {
		    define('CLI_SCRIPT', false);
		}

		// Set Output.
		if (CLI_SCRIPT) {
			if (isset($_SERVER['REMOTE_ADDR']) || php_sapi_name() != 'cli') {
				die("Must be run from CLI.");
			}

		    $OUTPUT = new \Rapid\Presentation\CLI();
		} else {
		    $OUTPUT = new \Rapid\Presentation\Output();
		}

		// This can be used for profiling.
		if (isset($CFG->_init_called)) {
			die("Init has already been called.");
		}

		$CFG->_init_called = microtime(true);

		// Try and set timezone if we don't have a default.
		if (!ini_get("date.timezone")) {
			ini_set("date.timezone", "UTC");
		}

		// Developer mode?
		if (!isset($CFG->developer_mode) || !$CFG->developer_mode) {
			static::init_error_handlers();
		}

		// Init everything else.
		if (!empty($CFG->database)) {
			static::init_db();
		}

		if (!empty($CFG->cache)) {
			static::init_cache();
		}
	}

	/**
	 * Initialize error handlers.
	 */
	public static function init_error_handlers() {
		@error_reporting(E_ALL);
		set_error_handler(array('\\Rapid\\Core', 'error_handler'), E_ALL);
		set_exception_handler(array('\\Rapid\\Core', 'handle_exception'));
	}

	/**
	 * Initialize Database.
	 */
	public static function init_db() {
		global $CFG, $DB;

		// DB connection.
		$DB = new \Rapid\Data\PDO(
		    $CFG->database['adapter'],
		    $CFG->database['host'],
		    $CFG->database['port'],
		    $CFG->database['database'],
		    $CFG->database['username'],
		    $CFG->database['password'],
		    $CFG->database['prefix']
		);
	}

	/**
	 * Initialize Cache.
	 */
	public static function init_cache() {
		global $CFG, $CACHE;

		// Cache connection.
		$CACHE = new \Rapid\Data\Memcached($CFG->cache['servers'], $CFG->cache['prefix']);
	}

	/**
	 * Handle an exception.
	 */
	public static function handle_exception($e) {
		global $OUTPUT;
		$OUTPUT->error_page($e->getMessage());
	}

	/**
	 * Error handler.
	 */
	public static function error_handler($errno, $errstr, $errfile, $errline, $errcontext) {
		global $PAGE;

		if (error_reporting() === 0) {
			return false;
		}

		$message = "Issue in {$errfile} ({$errline}): {$errstr}.";

		switch ($errno) {
			case E_STRICT:
			case E_DEPRECATED:
			case E_NOTICE:
			case E_WARNING:
			case E_USER_WARNING:
			case E_USER_NOTICE:
				if (isset($PAGE)) {
					$PAGE->notify($message);
				} else {
					echo $message;
				}
			break;

			case E_ERROR:
			case E_USER_ERROR:
				$OUTPUT->error_page($message);
			break;
		}

		// Allow PHP to do its thing.
		return false;
	}
}
