<?php
/**
 * Skeleton\Test\Unit class
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 */

namespace Skeleton\Test;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;

class Unit extends \PHPUnit_Framework_TestCase {

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
			$driver = RemoteWebDriver::create(Config::$selenium_hub, $capabilities);
			self::$my_webdriver = $driver;
			self::$my_webdriver->manage()->timeouts()->implicitlyWait(1);
		}

		return self::$my_webdriver;
	}

	/**
	 * This method is called after the last case in the test has been run.
	 * We can safely terminate the webdriver connection here.
	 *
	 * @access public
	 */
	public static function tearDownAfterClass() {
		echo "tearDownAfterClass";
		if (self::$my_webdriver !== null) {
			self::$my_webdriver->quit();
			self::$my_webdriver = null;
		}
	}
}
