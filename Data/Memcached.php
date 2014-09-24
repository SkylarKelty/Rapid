<?php
/**
 * Rapid Prototyping Framework in PHP.
 * 
 * @author Skylar Kelty <skylarkelty@gmail.com>
 */

namespace Data;

class Memcached extends \Memcached
{
	/**
	 * Constructor
	 */
	public function __construct($servers, $prefix = 'rapid_') {
		parent::__construct();

		$this->addServers($servers);
		$this->setOption(\Memcached::OPT_PREFIX_KEY, $prefix);
	}
}
