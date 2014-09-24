<?php
/**
 * Rapid Prototyping Framework in PHP.
 * 
 * @author Skylar Kelty <skylarkelty@gmail.com>
 */

namespace Models;

class Example extends \Data\DBModel
{
	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct('example');

		$this->add_field('id', 			\Data\Model::TYPE_INT, 			11,		false, true);
		$this->add_field('identifier', 	\Data\Model::TYPE_STRING, 		255);
		$this->add_field('start', 		\Data\Model::TYPE_INT, 			3);
		$this->add_field('end', 		\Data\Model::TYPE_INT, 			3);
		$this->add_field('created', 	\Data\Model::TYPE_TIMESTAMP, 	19,		true, true);
		$this->add_field('updated', 	\Data\Model::TYPE_TIMESTAMP, 	19,		true, true);
	}
}
