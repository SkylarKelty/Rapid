<?php
/**
 * Rapid Prototyping Framework in PHP.
 * 
 * @author Skylar Kelty <skylarkelty@gmail.com>
 */

namespace Rapid\Presentation;

/**
 * Basic output methods class.
 */
class Output
{
	private $outputstarted;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->outputstarted = false;
	}

	/**
	 * Prints a generic header.
	 */
	public function header() {
		global $PAGE;

		$this->outputstarted = true;

		$stylesheets = $PAGE->get_stylesheets();

		echo <<<HTML5
			<!DOCTYPE html>
			<html lang="en">
			  <head>
			    <meta charset="utf-8">
			    <meta http-equiv="X-UA-Compatible" content="IE=edge">
			    <meta name="viewport" content="width=device-width, initial-scale=1">
			    <title>{$PAGE->get_title()}</title>

			    $stylesheets

			    <!--[if lt IE 9]>
			      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
			      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
			    <![endif]-->
			  </head>
			  <body role="document">
HTML5;

		$this->navigation();

		echo <<<HTML5
    		<div class="container page-content" role="main">
HTML5;
	}

	/**
	 * Prints up the navigation structure.
	 */
	private function navigation() {
		global $CFG, $PAGE;

		$elements = $PAGE->get_navbar();

		$menu = $this->navigation_menu($elements);
		$title = $CFG->brand;

		echo <<<HTML5
			<div class="navbar navbar-default navbar-fixed-top" role="navigation">
				<div class="container">
					<div class="navbar-header">
						<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target=".navbar-collapse">
							<span class="sr-only">Toggle navigation</span>
							<span class="icon-bar"></span>
							<span class="icon-bar"></span>
							<span class="icon-bar"></span>
						</button>
						<a class="navbar-brand" href="#">$title</a>
					</div>
					<div class="navbar-collapse collapse">
						<ul class="nav navbar-nav">
							$menu
						</ul>
					</div>
				</div>
			</div>
HTML5;
	}

	/**
	 * Prints a nav menu.
	 */
	private function navigation_menu($menu) {
		global $CFG, $PAGE;

		$result = '';

		foreach ($menu as $name => $url) {
			$name = htmlentities($name);

			if (is_array($url)) {
				$submenu = $this->navigation_menu($url);
				$result .= <<<HTML5
					<li class="dropdown">
						<a href="#" class="dropdown-toggle" data-toggle="dropdown">$name <span class="caret"></span></a>
						<ul class="dropdown-menu" role="menu">$submenu</ul>
					</li>
HTML5;
				continue;
			}

			$url = htmlentities($url);

			if ($name == 'divider') {
				$result .= '<li class="divider"></li>';
				continue;
			}

			if ($name == 'header') {
				$result .= '<li class="dropdown-header">' . $url . '</li>';
				continue;
			}

			$li = '<li';
			if ($PAGE->is_active($url)) {
				$li .= ' class="active"';
			}

			$obj = new \Rapid\URL($url);
			$result .= $li . '><a href="' . $obj . '">' . $name . '</a></li>';
		}

		return $result;
	}

	/**
	 * Prints a generic heading.
	 */
	public function heading($name = null, $level = 1) {
		global $PAGE;

		if ($name === null) {
			$name = $PAGE->get_title();
		}

		$level = (int)$level;
		$name = htmlentities($name);
		echo "<h{$level}>{$name}</h{$level}>";
	}

	/**
	 * Renders notifications.
	 */
	private function render_notifications($notifications) {
		if (empty($notifications)) {
			return "";
		}

		$out = '<div class="panel panel-warning developer-notifications">
					<div class="panel-heading">
						<h3 class="panel-title">Developer Notifications</h3>
					</div>
					<div class="panel-body">';

		foreach ($notifications as $notification) {
			$out .= '<div class="alert alert-info alert-dismissible developer-notification" role="alert">';
			$out .= '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>';
			$out .= "{$notification}</div>";
		}

		$out .= '</div></div>';

		return $out;
	}

	/**
	 * Prints a footer.
	 */
	public function footer() {
		global $CFG, $PAGE;

		$scripts = $PAGE->get_javascript();

		if (isset($CFG->profiling_mode) && $CFG->profiling_mode) {
			$PAGE->notify("Page loaded in " . sprintf("%f", microtime(true) - $CFG->_init_called) . "s.");
		}

		$notifications = $PAGE->get_notifications();
		$notifications = $this->render_notifications($notifications);

		echo <<<HTML5
				</div>
			    $scripts
			    $notifications
			  </body>
			</html>
HTML5;
	}

	/**
	 * Print an error page.
	 */
	public function error_page($message) {
		if (!$this->outputstarted) {
			echo <<<HTML5
				<!DOCTYPE html>
				<html lang="en">
					<head>
					<meta charset="utf-8">
					<meta http-equiv="X-UA-Compatible" content="IE=edge">
					<meta name="viewport" content="width=device-width, initial-scale=1">
					<title>Oops!</title>

					<link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css" rel="stylesheet">
				</head>
				<body role="document">
					<div class="container page-content" role="main">
HTML5;
		}

		echo '<p>An error was encountered during execution.<br />The details are below...</p>';
		echo $message;
		print_r(debug_backtrace());

		if (!$this->outputstarted) {
			echo <<<HTML5
					</div>
				</body>
			</html>
HTML5;
		}

		die;
	}

	/**
	 * Escapes a string to be safe.
	 */
	public function escape_string($var) {
		return htmlspecialchars($var, ENT_QUOTES | ENT_HTML5 | ENT_SUBSTITUTE, 'UTF-8', false);
	}
}
