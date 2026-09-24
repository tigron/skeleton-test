<?php
/**
 * Selenium Driver implementation
 *
 * Wraps the Selenium\Webdriver session singleton and implements the
 * engine-agnostic Driver API on top of php-webdriver.
 *
 * @author Gerry Demaret <gerry@tigron.be>
 */

namespace Skeleton\Test\Driver;

use Facebook\WebDriver\Exception\NoSuchElementException;
use Skeleton\Test\Config;
use Skeleton\Test\Driver;
use Skeleton\Test\Page\Elementnotfound;
use Skeleton\Test\Selenium\Selector;
use Skeleton\Test\Selenium\Webdriver;
use Skeleton\Test\Selenium\Webdriver\Element;

class Selenium implements Driver {

	/**
	 * Webdriver session
	 *
	 * @access private
	 * @var Webdriver $webdriver
	 */
	private Webdriver $webdriver;

	/**
	 * Constructor
	 *
	 * @access public
	 */
	public function __construct() {
		$this->webdriver = Webdriver::initiate();
	}

	/**
	 * Open an URL
	 *
	 * @access public
	 * @param string $url
	 */
	public function open_url(string $url): void {
		$this->webdriver->get($url);
	}

	/**
	 * Refresh the current page
	 *
	 * @access public
	 */
	public function refresh(): void {
		$this->webdriver->navigate()->refresh();
	}

	/**
	 * Get the URL of the current page
	 *
	 * @access public
	 * @return string
	 */
	public function get_current_url(): string {
		return $this->webdriver->getCurrentURL();
	}

	/**
	 * Get the title of the current page
	 *
	 * @access public
	 * @return string
	 */
	public function get_title(): string {
		return $this->webdriver->getTitle();
	}

	/**
	 * Click an element
	 *
	 * @access public
	 * @param string $selector css or xpath selector
	 * @param string|null $within selector of a parent element to search in
	 */
	public function click(string $selector, ?string $within = null): void {
		$this->find_element($selector, $within, Config::$default_implicit_timeout)->click();
	}

	/**
	 * Clear an element and type a value into it
	 *
	 * @access public
	 * @param string $selector css or xpath selector
	 * @param string $value
	 * @param string|null $within selector of a parent element to search in
	 */
	public function fill(string $selector, string $value, ?string $within = null): void {
		$element = $this->find_element($selector, $within, Config::$default_implicit_timeout);
		$element->clear();
		$element->sendKeys($value);
	}

	/**
	 * Type a value into an element without clearing it first
	 *
	 * @access public
	 * @param string $selector css or xpath selector
	 * @param string $keys value or key sequence (WebDriverKeys)
	 * @param string|null $within selector of a parent element to search in
	 */
	public function send_keys(string $selector, string $keys, ?string $within = null): void {
		$this->find_element($selector, $within, Config::$default_implicit_timeout)->sendKeys($keys);
	}

	/**
	 * Clear an element
	 *
	 * @access public
	 * @param string $selector css or xpath selector
	 * @param string|null $within selector of a parent element to search in
	 */
	public function clear(string $selector, ?string $within = null): void {
		$this->find_element($selector, $within, Config::$default_implicit_timeout)->clear();
	}

	/**
	 * Get the text of an element
	 *
	 * @access public
	 * @param string $selector css or xpath selector
	 * @param string|null $within selector of a parent element to search in
	 * @return string
	 */
	public function get_text(string $selector, ?string $within = null): string {
		return $this->find_element($selector, $within, Config::$default_implicit_timeout)->getText();
	}

	/**
	 * Get an attribute of an element
	 *
	 * @access public
	 * @param string $selector css or xpath selector
	 * @param string $attribute
	 * @param string|null $within selector of a parent element to search in
	 * @return string|null
	 */
	public function get_attribute(string $selector, string $attribute, ?string $within = null): ?string {
		return $this->find_element($selector, $within, Config::$default_implicit_timeout)->getAttribute($attribute);
	}

	/**
	 * Is the element displayed
	 *
	 * @access public
	 * @param string $selector css or xpath selector
	 * @param string|null $within selector of a parent element to search in
	 * @return bool
	 */
	public function is_displayed(string $selector, ?string $within = null): bool {
		try {
			return $this->find_element($selector, $within, 0)->isDisplayed();
		} catch (Elementnotfound $e) {
			return false;
		}
	}

	/**
	 * Is the element selected
	 *
	 * @access public
	 * @param string $selector css or xpath selector
	 * @param string|null $within selector of a parent element to search in
	 * @return bool
	 */
	public function is_selected(string $selector, ?string $within = null): bool {
		try {
			return $this->find_element($selector, $within, 0)->isSelected();
		} catch (Elementnotfound $e) {
			return false;
		}
	}

	/**
	 * Is the element enabled
	 *
	 * @access public
	 * @param string $selector css or xpath selector
	 * @param string|null $within selector of a parent element to search in
	 * @return bool
	 */
	public function is_enabled(string $selector, ?string $within = null): bool {
		try {
			return $this->find_element($selector, $within, 0)->isEnabled();
		} catch (Elementnotfound $e) {
			return false;
		}
	}

	/**
	 * Count the elements matching a selector
	 *
	 * @access public
	 * @param string $selector css or xpath selector
	 * @param string|null $within selector of a parent element to search in
	 * @return int
	 */
	public function count(string $selector, ?string $within = null): int {
		if ($within !== null) {
			return count($this->find_element($within)->findElements(Selector::to_by($selector)));
		}

		return count($this->webdriver->findElements(Selector::to_by($selector)));
	}

	/**
	 * Hover over an element
	 *
	 * @access public
	 * @param string $selector css or xpath selector
	 * @param string|null $within selector of a parent element to search in
	 */
	public function hover(string $selector, ?string $within = null): void {
		$element = $this->find_element($selector, $within, Config::$default_implicit_timeout);
		$this->webdriver->action()->moveToElement($element)->perform();
	}

	/**
	 * Execute javascript in the browser
	 *
	 * @access public
	 * @param string $script
	 * @param array $arguments
	 * @return mixed
	 */
	public function execute_script(string $script, array $arguments = []): mixed {
		return $this->webdriver->executeScript($script, $arguments);
	}

	/**
	 * Has error
	 * Checks if the current page contains an error
	 *
	 * @access public
	 * @param string $error filled with the error text when an error is present
	 * @return bool
	 */
	public function has_error(&$error = ''): bool {
		$script = "if (document.querySelector('.exc-message') !== null) { return document.querySelector('#plain-exception').innerText } else { return false; }";
		$return = $this->webdriver->executeScript($script, []);

		if (!is_string($return) || $return === '') {
			return false;
		}

		$error = $return;
		return true;
	}

	/**
	 * Wait until the element is visible
	 *
	 * @access public
	 * @param string $selector css or xpath selector
	 * @param int $seconds
	 * @throws Elementnotfound when the element is not visible in time
	 */
	public function wait_until_visible(string $selector, int $seconds = 10): void {
		$this->poll(function () use ($selector) {
			return $this->is_displayed($selector);
		}, $seconds, 'Element not visible within ' . $seconds . ' seconds: ' . $selector);
	}

	/**
	 * Wait until the element is clickable
	 *
	 * @access public
	 * @param string $selector css or xpath selector
	 * @param int $seconds
	 * @throws Elementnotfound when the element is not clickable in time
	 */
	public function wait_until_clickable(string $selector, int $seconds = 10): void {
		$this->poll(function () use ($selector) {
			return $this->is_displayed($selector) && $this->is_enabled($selector);
		}, $seconds, 'Element not clickable within ' . $seconds . ' seconds: ' . $selector);
	}

	/**
	 * Wait until the element is present in the DOM
	 *
	 * @access public
	 * @param string $selector css or xpath selector
	 * @param int $seconds
	 * @throws Elementnotfound when the element is not present in time
	 */
	public function wait_until_present(string $selector, int $seconds = 10): void {
		$this->poll(function () use ($selector) {
			return $this->count($selector) > 0;
		}, $seconds, 'Element not present within ' . $seconds . ' seconds: ' . $selector);
	}

	/**
	 * Wait until the element is hidden or removed from the DOM
	 *
	 * @access public
	 * @param string $selector css or xpath selector
	 * @param int $seconds
	 * @throws Elementnotfound when the element is still visible in time
	 */
	public function wait_until_hidden(string $selector, int $seconds = 10): void {
		$this->poll(function () use ($selector) {
			return !$this->is_displayed($selector);
		}, $seconds, 'Element still visible within ' . $seconds . ' seconds: ' . $selector);
	}

	/**
	 * Wait until a condition is met
	 *
	 * @access public
	 * @param callable $predicate
	 * @param int $seconds
	 * @throws Elementnotfound when the condition is not met in time
	 */
	public function wait_until(callable $predicate, int $seconds = 10): void {
		$this->poll($predicate, $seconds, 'Condition not met within ' . $seconds . ' seconds');
	}

	/**
	 * Set the implicit timeout for element searches
	 *
	 * @access public
	 * @param int $seconds
	 */
	public function set_implicit_timeout(int $seconds): void {
		$this->webdriver->manage()->timeouts()->implicitlyWait($seconds);
	}

	/**
	 * Get the native driver object
	 *
	 * Escape hatch for driver-specific operations. The returned object is
	 * the RemoteWebDriver session.
	 *
	 * @access public
	 * @return Webdriver
	 */
	public function native(): Webdriver {
		return $this->webdriver;
	}

	/**
	 * Find an element, optionally scoped to a parent element
	 *
	 * The $timeout is used as implicit wait for the lookup. When null, the
	 * session default implicit wait applies.
	 *
	 * @access private
	 * @param string $selector css or xpath selector
	 * @param string|null $within selector of a parent element to search in
	 * @param int|null $timeout implicit wait in seconds
	 * @return Element
	 * @throws Elementnotfound when the element is not present
	 */
	private function find_element(string $selector, ?string $within = null, ?int $timeout = null): Element {
		if ($within !== null) {
			try {
				$parent = $this->webdriver->findElement(Selector::to_by($within), $timeout);
				return $parent->findElement(Selector::to_by($selector), $timeout);
			} catch (NoSuchElementException $e) {
				throw new Elementnotfound('Element not found: ' . $selector . ' within ' . $within);
			}
		}

		try {
			return $this->webdriver->findElement(Selector::to_by($selector), $timeout);
		} catch (NoSuchElementException $e) {
			throw new Elementnotfound('Element not found: ' . $selector);
		}
	}

	/**
	 * Wait until a condition is met
	 *
	 * The predicate is called every 100ms until it returns true or the
	 * timeout is reached. Elementnotfound thrown by the predicate is
	 * treated as false.
	 *
	 * @access private
	 * @param callable $predicate
	 * @param int $seconds
	 * @param string $message exception message on timeout
	 * @throws Elementnotfound when the condition is not met in time
	 */
	private function poll(callable $predicate, int $seconds, string $message): void {
		$deadline = microtime(true) + $seconds;

		while (microtime(true) < $deadline) {
			try {
				if ($predicate() === true) {
					return;
				}
			} catch (Elementnotfound $e) {
				// element not attached yet, retry
			}

			usleep(100000);
		}

		throw new Elementnotfound($message);
	}

	/**
	 * Quit all webdriver sessions
	 *
	 * @access public
	 */
	public static function quit_all(): void {
		Webdriver::quit_all();
	}
}
