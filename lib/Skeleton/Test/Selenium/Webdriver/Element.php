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
		$this->selenium_webdriver->page->check_error();
    }

	/**
	 * Find an element
	 *
	 * @access public
	 * @param Facebook\WebDriver\WebDriverBy $by
	 * @return Skeleton\Test\Selenium\Webdriver\Element $element
	 */
	public function findElement(WebDriverBy $by) {
        $element = parent::findElement($by);
		$element->selenium_webdriver = $this->selenium_webdriver;
		return $element;
	}

	/**
	 * Find elements
	 *
	 * @access public
	 * @param Facebook\WebDriver\WebDriverBy $by
	 * @return array $elements
	 */
	public function findElements(WebDriverBy $by) {
        $elements = parent::findElements($by);

        foreach ($elements as $key => $element) {
			$elements[$key]->selenium_webdriver = $this->selenium_webdriver;
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
