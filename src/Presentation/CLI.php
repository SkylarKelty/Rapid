<?php
/**
 * Rapid Prototyping Framework in PHP.
 * 
 * @author Skylar Kelty <skylarkelty@gmail.com>
 */

namespace Rapid\Presentation;

/**
 * CLI output methods class.
 */
class CLI
{
	/**
	 * Simple echo.
	 */
	public function send($text) {
		echo $text . "\n";
	}

	/**
	 * Simple banner.
	 */
	public function banner() {
		echo str_repeat('~', 50) . "\n";
	}
}
