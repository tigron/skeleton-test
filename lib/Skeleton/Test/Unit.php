<?php
/**
 * Skeleton\Test\Unit class
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 */

namespace Skeleton\Test;

class Unit extends \PHPUnit_Extensions_Selenium2TestCase {

	public static $browsers = [];


	public function run(PHPUnit_Framework_TestResult $result = null) {
		self::$browsers = [
		    [
		        'browserName' => 'firefox',
		        'host' => '10.1.0.52',
		        'port' => 4444,
		    ],
		    [
		        'browserName' => 'chrome',
		        'host' => '10.1.0.52',
		        'port' => 4444,
		    ],
		];

		parent::run($result);
	}
}
