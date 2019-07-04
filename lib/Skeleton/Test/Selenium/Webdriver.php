<?php
/**
 * Skeleton\Test\RWebdriver class
 *
 * @author Lionel Laffineur <lionel@tigron.be>
 */

namespace Skeleton\Test\Selenium;

use Facebook\WebDriver\WebDriverBy;

class Webdriver extends \Facebook\WebDriver\Remote\RemoteWebDriver {

	/**
	 * Page object
	 *
	 * @var Skeleton\Test\Page $page
	 * @access public
	 */
	public $page;

	/**
	 * Find an element
	 *
	 * @access public
	 * @param Facebook\WebDriver\WebDriverBy $by
	 * @param $timeout
	 * @return Skeleton\Test\Selenium\Webdriver\Element $element
	 */
	public function findElement(WebDriverBy $by, $timeout = null) {
		if ($timeout != null) {
			$this->manage()->timeouts()->implicitlyWait($timeout);
		}
        $src = parent::findElement($by);
		$dest = new \Skeleton\Test\Selenium\Webdriver\Element($this->executeMethod, $this->sessionID);
		$dest->import($src);
		$dest->selenium_webdriver = $this;

		if ($timeout != null) {
			$this->manage()->timeouts()->implicitlyWait(Config::$default_implicit_timeout);
		}
		return $dest;
	}

	/**
	 * Find elements
	 *
	 * @access public
	 * @param Facebook\WebDriver\WebDriverBy $by
	 * @param $timeout
	 * @return array $elements
	 */
	public function findElements(WebDriverBy $by, $timeout = null) {
		if ($timeout != null) {
			$this->manage()->timeouts()->implicitlyWait($timeout);
		}
        $sources = parent::findElements($by);
        $destinations = [];
        foreach ($sources as $source) {
			$dest = new \Skeleton\Test\Selenium\Webdriver\Element($this->executeMethod, $this->sessionID);
			$dest->import($source);
			$dest->selenium_webdriver = $this;
			$destinations[] = $dest;
        }

		if ($timeout != null) {
			$this->manage()->timeouts()->implicitlyWait(Config::$default_implicit_timeout);
		}
		return $destinations;
	}
}
