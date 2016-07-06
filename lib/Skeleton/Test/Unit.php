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
	public $webdriver = null;

	/**
	 * Create a new testcase
	 *
	 * @access public
	 */
	public function __construct() {
		$capabilities = DesiredCapabilities::chrome();
		$driver = RemoteWebDriver::create(Config::$selenium_hub, $capabilities, 5000);
		$this->webdriver = $driver;
	}

	/**
	 * Destruct the webdriver
	 *
	 * @access public
	 */
	public function __destruct() {
		$this->webdriver->quit();
	}
}
