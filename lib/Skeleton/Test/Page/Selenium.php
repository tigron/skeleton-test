<?php
/**
 * Page class
 *
 * @author Gerry Demaret <gerry@tigron.be>
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

namespace Skeleton\Test\Page;

abstract class Selenium extends \Skeleton\Test\Page {

	/**
	 * Webdriver variable
	 *
	 * @access protected
	 * @var \Skeleton\Test\Selenium\Webdriver $webdriver
	 */
	protected $webdriver = null;

	/**
	 * Construct
	 *
	 * @access public
	 */
	public function __construct() {
		$this->get_webdriver()->manage()->window()->maximize();
	}

	/**
	 * Get url
	 *
	 * @access public
	 * @return string $url
	 */
	abstract public function get_url();

	/**
	 * Open the page
	 *
	 * @access public
	 */
	public function open() {
		$this->get_webdriver()->get($this->get_url());
		$this->check_error();
	}

	/**
	 * Check for error and throw exception
	 *
	 * @access public
	 */
	public function check_error() {
		if ($this->has_error($error)) {
			throw new \Exception('Error on page: ' . "\n" . $error);
		}
	}

	/**
	 * Has error
	 * Checks if the current page contains an error
	 *
	 * @access public
	 * @return bool
	 */
	public function has_error(&$error = '') {
		$script = "if (document.querySelector('.exc-message') !== null) { return document.querySelector('#plain-exception').innerText } else { return false; }";
		$return = $this->get_webdriver()->executeScript($script, []);

		if ($return === false) {
			return false;
		}

		$error = $return;
		return true;
	}

	/**
	 * Find an element on the page
	 *
	 * @access public
	 * @param string $selector css or xpath selector
	 * @param int $timeout seconds to wait for the element to be attached, 0 = immediate
	 * @return \Skeleton\Test\Page\Element
	 * @throws \Skeleton\Test\Page\Elementnotfound when the element is not present
	 */
	public function find_element(string $selector, int $timeout = 0): \Skeleton\Test\Page\Element {
		try {
			$element = $this->get_webdriver()->findElement(\Skeleton\Test\Selenium\Selector::to_by($selector), $timeout * 1000);
		} catch (\Facebook\WebDriver\Exception\NoSuchElementException $e) {
			throw new \Skeleton\Test\Page\Elementnotfound('Element not found: ' . $selector);
		}

		return new \Skeleton\Test\Page\Selenium\Element($element);
	}

	/**
	 * Find elements on the page
	 *
	 * @access public
	 * @param string $selector css or xpath selector
	 * @param int $timeout seconds to wait for the elements to be attached, 0 = immediate
	 * @return array
	 */
	public function find_elements(string $selector, int $timeout = 0): array {
		try {
			$elements = $this->get_webdriver()->findElements(\Skeleton\Test\Selenium\Selector::to_by($selector), $timeout * 1000);
		} catch (\Facebook\WebDriver\Exception\NoSuchElementException $e) {
			return [];
		}

		$result = [];
		foreach ($elements as $element) {
			$result[] = new \Skeleton\Test\Page\Selenium\Element($element);
		}

		return $result;
	}

	/**
	 * Wait until the element is visible
	 *
	 * @access public
	 * @param string $selector css or xpath selector
	 * @param int $seconds
	 * @return \Skeleton\Test\Page\Element
	 * @throws \Skeleton\Test\Page\Elementnotfound when the element is not visible in time
	 */
	public function wait_until_visible(string $selector, int $seconds = 10): \Skeleton\Test\Page\Element {
		try {
			$this->get_webdriver()->wait($seconds, 100)->until(\Facebook\WebDriver\WebDriverExpectedCondition::visibilityOfElementLocated(\Skeleton\Test\Selenium\Selector::to_by($selector)));
		} catch (\Facebook\WebDriver\Exception\TimeoutException | \Facebook\WebDriver\Exception\NoSuchElementException $e) {
			throw new \Skeleton\Test\Page\Elementnotfound('Element not visible: ' . $selector);
		}

		return $this->find_element($selector);
	}

	/**
	 * Wait until the element is clickable
	 *
	 * @access public
	 * @param string $selector css or xpath selector
	 * @param int $seconds
	 * @return \Skeleton\Test\Page\Element
	 * @throws \Skeleton\Test\Page\Elementnotfound when the element is not clickable in time
	 */
	public function wait_until_clickable(string $selector, int $seconds = 10): \Skeleton\Test\Page\Element {
		try {
			$this->get_webdriver()->wait($seconds, 100)->until(\Facebook\WebDriver\WebDriverExpectedCondition::elementToBeClickable(\Skeleton\Test\Selenium\Selector::to_by($selector)));
		} catch (\Facebook\WebDriver\Exception\TimeoutException | \Facebook\WebDriver\Exception\NoSuchElementException $e) {
			throw new \Skeleton\Test\Page\Elementnotfound('Element not clickable: ' . $selector);
		}

		return $this->find_element($selector);
	}

	/**
	 * Wait until the element is present in the DOM
	 *
	 * @access public
	 * @param string $selector css or xpath selector
	 * @param int $seconds
	 * @return \Skeleton\Test\Page\Element
	 * @throws \Skeleton\Test\Page\Elementnotfound when the element is not present in time
	 */
	public function wait_until_present(string $selector, int $seconds = 10): \Skeleton\Test\Page\Element {
		try {
			$this->get_webdriver()->wait($seconds, 100)->until(\Facebook\WebDriver\WebDriverExpectedCondition::presenceOfElementLocated(\Skeleton\Test\Selenium\Selector::to_by($selector)));
		} catch (\Facebook\WebDriver\Exception\TimeoutException | \Facebook\WebDriver\Exception\NoSuchElementException $e) {
			throw new \Skeleton\Test\Page\Elementnotfound('Element not present: ' . $selector);
		}

		return $this->find_element($selector);
	}

	/**
	 * Wait until the element is hidden or removed from the DOM
	 *
	 * @access public
	 * @param string $selector css or xpath selector
	 * @param int $seconds
	 * @throws \Skeleton\Test\Page\Elementnotfound when the element is still visible after the timeout
	 */
	public function wait_until_hidden(string $selector, int $seconds = 10): void {
		try {
			$this->get_webdriver()->wait($seconds, 100)->until(\Facebook\WebDriver\WebDriverExpectedCondition::invisibilityOfElementLocated(\Skeleton\Test\Selenium\Selector::to_by($selector)));
		} catch (\Facebook\WebDriver\Exception\TimeoutException $e) {
			throw new \Skeleton\Test\Page\Elementnotfound('Element still visible: ' . $selector);
		}
	}

	/**
	 * Get the URL of the current page
	 *
	 * @access public
	 * @return string
	 */
	public function get_current_url(): string {
		return $this->get_webdriver()->getCurrentURL();
	}

	/**
	 * Set the implicit timeout for element searches
	 *
	 * @access public
	 * @param int $seconds
	 */
	public function set_implicit_timeout(int $seconds): void {
		$this->get_webdriver()->manage()->timeouts()->implicitlyWait($seconds);
	}

	/**
	 * Get webdriver
	 *
	 * @access protected
	 * @return Skeleton\Test\Selenium\Webdriver $webdriver
	 */
	protected function get_webdriver(): \Skeleton\Test\Selenium\Webdriver {
		if (empty($this->webdriver)) {
			$this->webdriver = \Skeleton\Test\Selenium\Webdriver::initiate();
			$this->webdriver->page = $this;
		}
		return $this->webdriver;
	}
}
