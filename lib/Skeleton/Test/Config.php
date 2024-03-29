<?php
/**
 * Config class
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author Lionel Laffineur <lionel@tigron.be>
 */

namespace Skeleton\Test;

class Config {

	/**
	 * Browser
	 *
	 * @access public
	 * @var string $browser
	 */
	public static $browser = 'firefox';

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
	 * @deprecated use $test_path instead
	 * @var string $test_directory
	 */
	public static $test_directory = null;

	/**
	 * Test path
	 *
	 * This folder will be used to store the tests
	 *
	 * @access public
	 * @var string $test_path
	 */
	public static $test_path = null;

	/**
	 * Name of the file where the start timestamp will be stored
	 *
	 * This file should be filled in with the timestamp when test starts (i.e.: date +%s > /tmp/skeleton-test-start-time.txt)
	 *
	 * @access public
	 * @var string $start_timestamp_filename
	 */
	public static $start_timestamp_filename = null;

	/**
	 * Name of the file where the timings will be saved
	 *
	 * This file will be filled in with the start and stop timings of every test in json format
	 *
	 * @access public
	 * @var string $timings_filename
	 */
	public static $timings_filename = null;

	/**
	 * Default implicit timeout when waiting for an object in the dom
	 *
	 * @access public
	 * @var int $default_implicit_timeout
	 */
	public static $default_implicit_timeout = 5;

	/**
	 * Intense test count
	 *
	 * @access public
	 * @var int $default_intense_count
	 */
	public static $intense_count = 10;
}
