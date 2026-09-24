<?php
/**
 * Skeleton\Test\Selenium\Webdriver\Element class
 *
 * @author Lionel Laffineur <lionel@tigron.be>
 */

namespace Skeleton\Test\Selenium\Webdriver;

use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\WebDriverBy;
use Skeleton\Test\Config;

class Element extends \Facebook\WebDriver\Remote\RemoteWebElement {

	/**
	 * Webdriver session
	 *
	 * @access public
	 * @var Webdriver
	 */
	public $webdriver;

	/**
	 * Click the element
	 *
	 * @access public
	 */
	public function click(): void {
		parent::click();
	}

	/**
	 * Find an element
	 *
	 * @access public
	 * @param WebDriverBy $by
	 * @param int|null $timeout
	 * @return Element
	 */
	public function findElement(WebDriverBy $by, $timeout = null): Element {
		if ($timeout !== null) {
			$this->webdriver->manage()->timeouts()->implicitlyWait($timeout);
		}

		$element = parent::findElement($by);
		$element->webdriver = $this->webdriver;

		if ($timeout !== null) {
			$this->webdriver->manage()->timeouts()->implicitlyWait(Config::$default_implicit_timeout);
		}

		return $element;
	}

	/**
	 * Find elements
	 *
	 * @access public
	 * @param WebDriverBy $by
	 * @param int|null $timeout
	 * @return array
	 */
	public function findElements(WebDriverBy $by, $timeout = null): array {
		if ($timeout !== null) {
			$this->webdriver->manage()->timeouts()->implicitlyWait($timeout);
		}

		$elements = parent::findElements($by);

		foreach ($elements as $key => $element) {
			$elements[$key]->webdriver = $this->webdriver;
		}

		if ($timeout !== null) {
			$this->webdriver->manage()->timeouts()->implicitlyWait(Config::$default_implicit_timeout);
		}

		return $elements;
	}

	/**
	 * Import the properties
	 *
	 * @access public
	 * @param RemoteWebElement $src
	 */
	public function import($src): void {
		foreach (get_object_vars($src) as $key => $value) {
			$this->$key = $value;
		}
	}
}
