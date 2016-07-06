<?php
/**
 * Config class
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 */

namespace Skeleton\Test;

class Config {

	/**
	 * Browsers
	 *
	 * @access public
	 * @var array $browsers
	 */
	public static $selenium_hub = 'http://localhost:4444/wd/hub';

	/**
	 * Test directory
	 *
	 * This folder will be used to store the tests
	 *
	 * @access public
	 * @var string $test_directory
	 */
	public static $test_directory = null;

}
