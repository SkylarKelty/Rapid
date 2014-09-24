<?php
/**
 * Rapid Prototyping Framework in PHP.
 * 
 * @author Skylar Kelty <skylarkelty@gmail.com>
 */

namespace Presentation;

/**
 * Basic output methods class.
 */
class Output
{
	/**
	 * Prints a generic header.
	 */
	public function header() {
		global $PAGE;

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

			$obj = new \URL($url);
			$result .= $li . '><a href="' . $obj . '">' . $name . '</a></li>';
		}

		return $result;
	}

	/**
	 * Prints a generic heading.
	 */
	public function heading($name, $level = 1) {
		$level = (int)$level;
		$name = htmlentities($name);
		echo "<h{$level}>{$name}</h{$level}>";
	}

	/**
	 * Prints a footer.
	 */
	public function footer() {
		global $PAGE;

		$scripts = $PAGE->get_javascript();

		echo <<<HTML5
				</div>
			    $scripts
			  </body>
			</html>
HTML5;
	}
}
