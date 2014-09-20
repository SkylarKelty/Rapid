<?php
/**
 * CLA system mock-up
 * 
 * @author Skylar Kelty <skylarkelty@gmail.com>
 */

global $CFG;

$CFG = new \stdClass();
$CFG->dirroot = dirname(__FILE__);
$CFG->cssroot = $CFG->dirroot . '/styles';
$CFG->wwwroot = 'http://kent.moodle:8080/cla';

// Register the autoloader now.
spl_autoload_register(function($class) {
	global $CFG;

	$parts = explode('\\', $class);

	$filename = $CFG->dirroot . '/classes/' . implode('/', $parts) . '.php';
	if (file_exists($filename)) {
		require_once($filename);
	}
});

// Register the composer autoloaders.
require_once($CFG->dirroot . '/vendor/autoload.php');

// DB connection.
$DB = new \DML\MySQLi('mysql:host=localhost;port=3306;dbname=connect_development', 'root', '');

// Output library.
$OUTPUT = new \Presentation\Output();

// Page library.
$PAGE = new \Presentation\Page();

// Set a default page title.
$PAGE->set_title('CLA');