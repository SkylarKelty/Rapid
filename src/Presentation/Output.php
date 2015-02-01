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
		global $CFG, $PAGE;

		$this->outputstarted = true;

		$stylesheets = $PAGE->get_stylesheets();

		$out = <<<HTML5
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

		$navelements = $PAGE->get_navbar();
		$out .= $this->navigation($CFG->brand, $navelements);

		$out .= <<<HTML5
    		<div class="container page-content" role="main">
HTML5;

		return $out;
	}

	/**
	 * Prints up the navigation structure.
	 */
	private function navigation($title, $elements, $classes = "navbar-default navbar-fixed-top") {
		$menu = $this->navigation_menu($elements);

		return <<<HTML5
			<div class="navbar $classes" role="navigation">
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

			if ($name == 'divider') {
				$result .= '<li class="divider"></li>';
				continue;
			}

			if ($name == 'header') {
				$result .= '<li class="dropdown-header">' . $this->escape_string($url) . '</li>';
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
		return "<h{$level}>{$name}</h{$level}>";
	}

	/**
	 * Renders an alert.
	 */
	public function alert($text, $type = 'info', $dismissable = false) {
		$str = '';
		if ($dismissable) {
			$str .= "<div class=\"alert alert-{$type} alert-dismissible developer-notification\" role=\"alert\">";
			$str .= '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>';
			$str .= "{$text}</div>";
		} else {
			$str .= "<div class=\"alert alert-{$type}\" role=\"alert\">{$text}</div>";
		}
		return $str;
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

		return <<<HTML5
				</div>
			    $scripts
			    $notifications
			  </body>
			</html>
HTML5;
	}

	/**
	 * Escapes a string to be safe.
	 */
	public function escape_string($var) {
		return htmlspecialchars($var, ENT_QUOTES | ENT_HTML5 | ENT_SUBSTITUTE, 'UTF-8', false);
	}

	/**
	 * Pretty-print a debug backtrace line.
	 */
	private function print_debug_trace($trace) {
		$body = '';
		foreach ($trace['args'] as $arg) {
			if ($arg instanceof \Exception) {
				$trace = $arg->getTrace();
				$trace = $trace[0];
				$body .= '<p>' . $arg->getMessage() . '</p>';
			} else {
				$body .= '<p>' . (string)$arg . '</p>';
			}
		}

		$method = '';
		if (isset($trace['class'])) {
			$method .= $trace['class'];
		}
		if (isset($trace['type'])) {
			$method .= $trace['type'];
		}
		if (isset($trace['function'])) {
			$method .= $trace['function'] . '()';
		}

		if ($method == 'Rapid\Presentation\Output->error_page()') {
			return;
		}

		if (isset($trace['file'])) {
			$filename = $trace['file'] . ' (' . $trace['line'] . ')';
			$title = "{$filename} - {$method}";
		} else {
			$title = $method;
		}

		return <<<HTML5
			<div class="panel panel-danger" role="alert">
				<div class="panel-heading">{$title}</div>
				<div class="panel-body">{$body}</div>
			</div>
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
		echo '<p>' . $message . '</p>';

		$backtrace = debug_backtrace();
		foreach ($backtrace as $level => $trace) {
			$this->print_debug_trace($trace);
		}

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
	 * Output a time() as a string.
	 */
	public function render_time($timestamp) {
		return strftime("%R%P %d/%m/%Y", $timestamp);
	}

	/**
	 * Convert timestamp to contextual time
	 * 
	 * @author Pete Karl II (http://peterthelion.com/)
	 * @link http://snipt.net/pkarl/pkarlcom-contextualtime/
	 * @link http://pkarl.com/articles/contextual-user-friendly-time-and-dates-php/
	 * @link https://gist.github.com/hakre/2397187
	 * @param int $timestamp The timestamp to return
	 */
	public function render_contextual_time($timestamp) {
		$n = time() - $timestamp;

		if ($n <= 1) {
			return 'less than 1 second ago';
		}

		if ($n < (60)) {
			return $n . ' seconds ago';
		}

		if ($n < (60 * 60)) {
			$minutes = round($n / 60);
			return 'about ' . $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ago';
		}

		if ($n < (60 * 60 * 16)) {
			$hours = round($n / (60 * 60));
			return 'about ' . $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
		}

		if ($n < (time() - strtotime('yesterday'))) {
			return 'yesterday';
		}

		if ($n < (60 * 60 * 24)) {
			$hours = round($n / (60 * 60));
			return 'about ' . $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
		}

		if ($n < (60 * 60 * 24 * 6.5)) {
			return 'about ' . round($n / (60 * 60 * 24)) . ' days ago';
		}

		if ($n < (time() - strtotime('last week'))) {
			return 'last week';
		}

		if (round($n / (60 * 60 * 24 * 7)) == 1) {
			return 'about a week ago';
		}

		if ($n < (60 * 60 * 24 * 7 * 3.5)) {
			return 'about ' . round($n / (60 * 60 * 24 * 7)) . ' weeks ago';
		}

		if ($n < (time() - strtotime('last month'))) {
			return 'last month';
		}

		if (round($n / (60 * 60 * 24 * 7 * 4)) == 1) {
			return 'about a month ago';
		}

		if ($n < (60 * 60 * 24 * 7 * 4 * 11.5)) {
			return 'about ' . round($n / (60 * 60 * 24 * 7 * 4)) . ' months ago';
		}

		if ($n < (time() - strtotime('last year'))) {
			return 'last year';
		}

		if (round($n / (60 * 60 * 24 * 7 * 52)) == 1) {
			return 'about a year ago';
		}

		if ($n >= (60 * 60 * 24 * 7 * 4 * 12)) {
			return 'about ' . round($n / (60 * 60 * 24 * 7 * 52)) . ' years ago';
		}

		return false;
	}
}
