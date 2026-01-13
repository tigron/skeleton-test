<?php
/**
 * Skeleton\Test\RWebdriverElement class
 *
 * @author Lionel Laffineur <lionel@tigron.be>
 */

namespace Skeleton\Test\Selenium\Webdriver;

use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

class Element extends \Facebook\WebDriver\Remote\RemoteWebElement {

	/**
	 * remotewebdriver
	 *
	 * @access public
	 * @var Skeleton\Test\Webdriver
	 */
	public $webdriver;

	/**
	 * Click the element
	 *
	 * @access public
	 * @param array $options
	 */
	public function click() {
		parent::click();
	}

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
		$element = parent::findElement($by);
		$element->webdriver = $this->webdriver;

		if ($timeout != null) {
			$this->manage()->timeouts()->implicitlyWait(Config::$default_implicit_timeout);
		}
		return $element;
	}

	/**
	 * Find elements
	 *
	 * @access public
	 * @param Facebook\WebDriver\WebDriverBy $by
	 * @return array $elements
	 */
	public function findElements(WebDriverBy $by, $timeout = null) {
		if ($timeout != null) {
			$this->manage()->timeouts()->implicitlyWait($timeout);
		}
        $elements = parent::findElements($by);

        foreach ($elements as $key => $element) {
			$elements[$key]->webdriver = $this->webdriver;
        }

		if ($timeout != null) {
			$this->manage()->timeouts()->implicitlyWait(Config::$default_implicit_timeout);
		}
		return $elements;
	}

    /**
     * Import the properties
     *
     * @access public
     * @param array $src
     */
    public function import($src) {
		foreach (get_object_vars($src) as $key => $value) {
            $this->$key = $value;
        }
    }
}
