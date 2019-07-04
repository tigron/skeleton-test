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
	 * @var Skeleton\Test\RWebdriverElement\RWebdriver
	 */
	public $selenium_webdriver;

	/**
	 * Click the element
	 *
	 * @access public
	 */
    public function click() {
    	$this->selenium_webdriver->wait(10, 2000)->until(
    		WebDriverExpectedCondition::visibilityOf($this)
    	);
    	if (!$this->isEnabled()) {
    		throw new \Exception('The element you try to click is not enabled');
    	}

        parent::click();
        $webdriver = $this->selenium_webdriver;
        $this->selenium_webdriver->wait()->until(
        	function () use ($webdriver) {
				$state = $webdriver->executeScript("return document.readyState;", [ ]);
				if ($state == 'complete') {
					return true;
				} else {
					return false;
				}
        	},
        	'Error: document doesn\'t enter readyState after click'
        );
		$this->selenium_webdriver->page->check_error();
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
		$element->selenium_webdriver = $this->selenium_webdriver;

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
			$elements[$key]->selenium_webdriver = $this->selenium_webdriver;
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
