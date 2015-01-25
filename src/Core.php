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
	 * Handle an exception.
	 */
	public static function handle_exception($e) {
		global $PAGE;

		$PAGE->notify((string)$e);
	}

	/**
	 * Error handler.
	 */
	public static function error_handler($errno, $errstr, $errfile, $errline, $errcontext) {
		global $PAGE;

		switch ($errno) {
			case E_STRICT:
			case E_DEPRECATED:
			case E_NOTICE:
			case E_WARNING:
			case E_USER_WARNING:
			case E_USER_NOTICE:
				$message = "Issue in {$errfile} ({$errline}): {$errstr}.";
				if (isset($PAGE)) {
					$PAGE->notify($message);
				} else {
					echo $message;
				}
			break;

			case E_ERROR:
			case E_USER_ERROR:
				// TODO - fast exit a nice Page.
			break;
		}

		// Allow PHP to do its thing.
		return false;
	}
}
