<?php
/**
 * Skeleton\Test\Unit class
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 */

namespace Skeleton\Test;

class Unit extends \PHPUnit_Extensions_Selenium2TestCase {

	/**
	 * Get the browser configuration
	 *
	 * @access public
	 * @return array $browsers
	 */
	public static function browsers() {
		return Config::$browsers;
	}

}
