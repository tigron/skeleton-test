<?php
/**
 * Driver interface
 *
 * The driver is the engine-agnostic API a Page uses to interact with the
 * browser. Two implementations ship with skeleton-test: selenium and
 * playwright.
 *
 * Selectors are css by default; selectors starting with '/' or '(' are
 * treated as xpath. An optional $within selector scopes the lookup to a
 * parent element.
 *
 * Actions (click, fill, get_text, ...) throw Elementnotfound when the
 * element is not present. The is_*() methods and count() never throw, they
 * return false or 0 instead.
 *
 * @author Gerry Demaret <gerry@tigron.be>
 */

namespace Skeleton\Test;

interface Driver {

	/**
	 * Open an URL
	 *
	 * @access public
	 * @param string $url
	 */
	public function open_url(string $url): void;

	/**
	 * Refresh the current page
	 *
	 * @access public
	 */
	public function refresh(): void;

	/**
	 * Get the URL of the current page
	 *
	 * @access public
	 * @return string
	 */
	public function get_current_url(): string;

	/**
	 * Get the title of the current page
	 *
	 * @access public
	 * @return string
	 */
	public function get_title(): string;

	/**
	 * Click an element
	 *
	 * @access public
	 * @param string $selector css or xpath selector
	 * @param string|null $within selector of a parent element to search in
	 */
	public function click(string $selector, ?string $within = null): void;

	/**
	 * Clear an element and type a value into it
	 *
	 * @access public
	 * @param string $selector css or xpath selector
	 * @param string $value
	 * @param string|null $within selector of a parent element to search in
	 */
	public function fill(string $selector, string $value, ?string $within = null): void;

	/**
	 * Type a value into an element without clearing it first
	 *
	 * @access public
	 * @param string $selector css or xpath selector
	 * @param string $keys value or key sequence (WebDriverKeys on selenium)
	 * @param string|null $within selector of a parent element to search in
	 */
	public function send_keys(string $selector, string $keys, ?string $within = null): void;

	/**
	 * Clear an element
	 *
	 * @access public
	 * @param string $selector css or xpath selector
	 * @param string|null $within selector of a parent element to search in
	 */
	public function clear(string $selector, ?string $within = null): void;

	/**
	 * Get the text of an element
	 *
	 * @access public
	 * @param string $selector css or xpath selector
	 * @param string|null $within selector of a parent element to search in
	 * @return string
	 */
	public function get_text(string $selector, ?string $within = null): string;

	/**
	 * Get an attribute of an element
	 *
	 * @access public
	 * @param string $selector css or xpath selector
	 * @param string $attribute
	 * @param string|null $within selector of a parent element to search in
	 * @return string|null
	 */
	public function get_attribute(string $selector, string $attribute, ?string $within = null): ?string;

	/**
	 * Is the element displayed
	 *
	 * Returns false when the element is absent or hidden, never throws.
	 *
	 * @access public
	 * @param string $selector css or xpath selector
	 * @param string|null $within selector of a parent element to search in
	 * @return bool
	 */
	public function is_displayed(string $selector, ?string $within = null): bool;

	/**
	 * Is the element selected
	 *
	 * Returns false when the element is absent, never throws.
	 *
	 * @access public
	 * @param string $selector css or xpath selector
	 * @param string|null $within selector of a parent element to search in
	 * @return bool
	 */
	public function is_selected(string $selector, ?string $within = null): bool;

	/**
	 * Is the element enabled
	 *
	 * Returns false when the element is absent, never throws.
	 *
	 * @access public
	 * @param string $selector css or xpath selector
	 * @param string|null $within selector of a parent element to search in
	 * @return bool
	 */
	public function is_enabled(string $selector, ?string $within = null): bool;

	/**
	 * Count the elements matching a selector
	 *
	 * @access public
	 * @param string $selector css or xpath selector
	 * @param string|null $within selector of a parent element to search in
	 * @return int
	 */
	public function count(string $selector, ?string $within = null): int;

	/**
	 * Hover over an element
	 *
	 * @access public
	 * @param string $selector css or xpath selector
	 * @param string|null $within selector of a parent element to search in
	 */
	public function hover(string $selector, ?string $within = null): void;

	/**
	 * Execute javascript in the browser
	 *
	 * The script is given in body style (as in selenium executeScript): it
	 * may contain return statements and access the passed $arguments as
	 * arguments[0], arguments[1], ...
	 *
	 * @access public
	 * @param string $script
	 * @param array $arguments
	 * @return mixed
	 */
	public function execute_script(string $script, array $arguments = []): mixed;

	/**
	 * Has error
	 * Checks if the current page contains an error
	 *
	 * @access public
	 * @param string $error filled with the error text when an error is present
	 * @return bool
	 */
	public function has_error(&$error = ''): bool;

	/**
	 * Wait until the element is visible
	 *
	 * @access public
	 * @param string $selector css or xpath selector
	 * @param int $seconds
	 * @throws Elementnotfound when the element is not visible in time
	 */
	public function wait_until_visible(string $selector, int $seconds = 10): void;

	/**
	 * Wait until the element is clickable
	 *
	 * @access public
	 * @param string $selector css or xpath selector
	 * @param int $seconds
	 * @throws Elementnotfound when the element is not clickable in time
	 */
	public function wait_until_clickable(string $selector, int $seconds = 10): void;

	/**
	 * Wait until the element is present in the DOM
	 *
	 * @access public
	 * @param string $selector css or xpath selector
	 * @param int $seconds
	 * @throws Elementnotfound when the element is not present in time
	 */
	public function wait_until_present(string $selector, int $seconds = 10): void;

	/**
	 * Wait until the element is hidden or removed from the DOM
	 *
	 * @access public
	 * @param string $selector css or xpath selector
	 * @param int $seconds
	 * @throws Elementnotfound when the element is still visible in time
	 */
	public function wait_until_hidden(string $selector, int $seconds = 10): void;

	/**
	 * Wait until a condition is met
	 *
	 * The predicate is called repeatedly until it returns true or the
	 * timeout is reached. Elementnotfound thrown by the predicate is
	 * treated as false.
	 *
	 * @access public
	 * @param callable $predicate
	 * @param int $seconds
	 * @throws Elementnotfound when the condition is not met in time
	 */
	public function wait_until(callable $predicate, int $seconds = 10): void;

	/**
	 * Set the implicit timeout for element searches
	 *
	 * Playwright has no implicit timeouts, this is a no-op there.
	 *
	 * @access public
	 * @param int $seconds
	 */
	public function set_implicit_timeout(int $seconds): void;

	/**
	 * Get the native driver object
	 *
	 * Escape hatch for driver-specific operations. The returned object is
	 * the RemoteWebDriver on selenium and the Playwright page on
	 * playwright. Code using this is coupled to the driver in use.
	 *
	 * @access public
	 * @return mixed
	 */
	public function native(): mixed;
}
