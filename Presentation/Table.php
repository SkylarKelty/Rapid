<?php
/**
 * Rapid Prototyping Framework in PHP.
 * 
 * @author Skylar Kelty <skylarkelty@gmail.com>
 */

namespace Presentation;

/**
 * Basic table methods class.
 */
class Table
{
	private $headings;
	private $rows;

	/**
	 * Import a bunch of data.
	 */
	public function set_data($rows) {
		if (!is_array($rows)) {
			$rows = (array)$rows;
		}

		$this->rows = array();
		foreach ($rows as $row) {
			$row = (array)$row;

			if (empty($this->headings)) {
				$this->headings = array_keys($row);
			}

			$this->rows[] = $row;
		}
	}

	/**
	 * Returns the table as a string.
	 */
	public function __toString() {
		$table = <<<HTML
			<table class="table table-striped">
				<thead>
HTML;

		foreach ($this->headings as $heading) {
			$table .= "<th>{$heading}</th>";
		}

		$table .= <<<HTML
				</thead>
				<tbody>
HTML;

		foreach ($this->rows as $row) {
			$table .= "<tr>";
			foreach ($row as $k => $v) {
				$table .= "<td>{$v}</td>";
			}
			$table .= "</tr>";
		}

		$table .= <<<HTML
				</tbody>
			</table>
HTML;

	return $table;
	}
}
