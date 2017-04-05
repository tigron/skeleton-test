<?php
/**
 * Skeleton\Test\Unit class
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author Lionel Laffineur <lionel@tigron.be>
 */

namespace Skeleton\Test;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;

class Unit extends \PHPUnit_Framework_TestCase {

	private static $start_timestamp_filename = '/var/tmp/tigron-skeleton-test.start';
	private static $timings_filename = '/var/tmp/tigron-skeleton-test.json';

	/**
	 * The webdriver variable
	 *
	 * @access public
	 * @var Facebook\WebDriver\Remote\RemoteWebDriver $webdriver
	 */
	private static $my_webdriver = null;

	/**
	 * Catch calls to the "webdriver" property and proxy them to our
	 * get_webdriver() method.
	 *
	 * @access public
	 * @param string $key
	 * @return mixed
	 */
	public function __get($key) {
		if ($key === 'webdriver') {
			return self::get_webdriver();
		}
	}

	/**
	 * Initialize a new webdriver connection if needed
	 *
	 * @access public
	 */
	public static function get_webdriver() {
		if (self::$my_webdriver === null) {
			$capabilities = DesiredCapabilities::chrome();
			$driver = \Skeleton\Test\Selenium\Webdriver::create(Config::$selenium_hub, $capabilities);
			self::$my_webdriver = $driver;
			self::$my_webdriver->manage()->timeouts()->implicitlyWait(5);
		}

		return self::$my_webdriver;
	}

	/**
	 * setupBeforeScene
	 * function called before a scene to init resources used in the scene
	 *
	 * @access public
	 */
	public static function setupBeforeScene() {
	}

	/**
	 * This method is called before the first case in the test.
	 *
	 * @access public
	 */
	public static function setUpBeforeClass() {
		$class = get_called_class();

		if (file_exists(self::$start_timestamp_filename)) {
			$timestamp = round(time() - file_get_contents(self::$start_timestamp_filename));
			$time = sprintf("%02d:%02d:%02d", ($timestamp/3600), ($timestamp/60%60), $timestamp%60);
			$timings = [];
			if (file_exists(self::$timings_filename)) {
				$timings = json_decode(file_get_contents(self::$timings_filename), true);
			}
			$data = [];
			if (isset($timings[$class])) {
				$data = $timings[$class];
			}
			$data['start_timestamp'] = $timestamp;
			$data['start_time'] = $time;
			$timings[$class] = $data;
			file_put_contents(self::$timings_filename, json_encode($timings));
		}

		try {
			$class::setupBeforeScene();
		} catch(Exception $e) {
		}
	}

	/**
	 * tearDownAfterScene
	 * function called after a scene to clean resources used during the scene
	 *
	 * @access public
	 */
	public static function tearDownAfterScene() {
	}

	/**
	 * This method is called after the last case in the test has been run.
	 * We can safely terminate the webdriver connection here.
	 *
	 * @access public
	 */
	public static function tearDownAfterClass() {
		$class = get_called_class();

		if (file_exists(self::$start_timestamp_filename)) {
			$timestamp = round(time() - file_get_contents(self::$start_timestamp_filename));
			$time = sprintf("%02d:%02d:%02d", ($timestamp/3600), ($timestamp/60%60), $timestamp%60);
			$timings = [];
			if (file_exists(self::$timings_filename)) {
				$timings = json_decode(file_get_contents(self::$timings_filename), true);
			}
			$data = [];
			if (isset($timings[$class])) {
				$data = $timings[$class];
			}
			$data['stop_timestamp'] = $timestamp;
			$data['stop_time'] = $time;
			$timings[$class] = $data;
			file_put_contents(self::$timings_filename, json_encode($timings));
		}

		try {
			$class::tearDownAfterScene();
		} catch(Exception $e) {
		}

		if (self::$my_webdriver !== null) {
			self::$my_webdriver->quit();
			self::$my_webdriver = null;
		}
	}
}
