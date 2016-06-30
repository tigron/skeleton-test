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
	public static $browsers = [];

	/**
	 * Test directory
	 *
	 * This folder will be used to store the tests
	 *
	 * @access public
	 * @var string $test_directory
	 */
	public static $test_directory = null;

	/**
	 * Add browser configuration
	 *
	 * @access public
	 * @param string $name
	 * @param string $host
	 * @param int $port
	 * @param int $timeout
	 */
	public static function add_browser_configuration($name, $host = 'localhost', $port = 4444, $timeout = 30000) {
		$configuration = [
			'browserName' => $name,
			'host' => $host,
			'port' => $port,
			'timeout' => $timeout
		];
		self::$browsers[] = $configuration;
	}
}
