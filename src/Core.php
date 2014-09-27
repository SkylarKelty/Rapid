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
		global $OUTPUT;
		$OUTPUT->error_page((string)$e);
	}
}
