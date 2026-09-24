<?php
/**
 * Page class
 *
 * Base class for all page objects. A page is engine-agnostic: it is written
 * against the Driver API and uses the driver declared by the scene that
 * instantiates it. A scene runs on a single driver; mixing drivers within
 * one scene throws.
 *
 * @author Gerry Demaret <gerry@tigron.be>
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

namespace Skeleton\Test;

use Skeleton\Test\Driver\Playwright;
use Skeleton\Test\Driver\Selenium;

abstract class Page {

	/**
	 * Driver instance
	 *
	 * @access protected
	 * @var Driver $driver
	 */
	protected Driver $driver;

	/**
	 * Scene class currently running
	 *
	 * @access private
	 * @var string|null $current_scene
	 */
	private static ?string $current_scene = null;

	/**
	 * Driver name resolved for the current scene
	 *
	 * @access private
	 * @var string|null $scene_driver
	 */
	private static ?string $scene_driver = null;

	/**
	 * Constructor
	 *
	 * @access public
	 * @param string|null $driver_name driver to use, defaults to the driver of the active scene
	 */
	public function __construct(?string $driver_name = null) {
		if ($driver_name === null) {
			$driver_name = self::$scene_driver;
		}

		if ($driver_name === null) {
			throw new \Exception('No driver specified and no scene is active');
		}

		if (self::$scene_driver !== null && $driver_name !== self::$scene_driver) {
			throw new \Exception('Scene ' . self::$current_scene . ' mixes drivers: scene runs on ' . self::$scene_driver . ', page requested ' . $driver_name);
		}

		$this->driver = match ($driver_name) {
			'selenium' => new Selenium(),
			'playwright' => new Playwright(get_class($this), self::$current_scene ?? $driver_name),
			default => throw new \Exception('Unknown driver: ' . $driver_name),
		};
	}

	/**
	 * Get the driver instance
	 *
	 * Escape hatch for driver-specific operations.
	 *
	 * @access public
	 * @return Driver
	 */
	public function get_driver(): Driver {
		return $this->driver;
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
	public function open(): void {
		$this->driver->open_url($this->get_url());
		$this->check_error();
	}

	/**
	 * Check for error and throw exception
	 *
	 * @access public
	 */
	public function check_error(): void {
		$error = '';
		if ($this->has_error($error)) {
			throw new \Exception('Error on page: ' . "\n" . $error);
		}
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
		return $this->driver->has_error($error);
	}

	/**
	 * Open an URL
	 *
	 * Navigates to the URL and returns whether the page loaded without errors.
	 *
	 * @access public
	 * @param string $url
	 * @return bool
	 */
	public function open_url(string $url): bool {
		$this->driver->open_url($url);

		return !$this->has_error();
	}

	/**
	 * Refresh the current page
	 *
	 * @access public
	 */
	public function refresh(): void {
		$this->driver->refresh();
	}

	/**
	 * Get the URL of the current page
	 *
	 * @access public
	 * @return string
	 */
	public function get_current_url(): string {
		return $this->driver->get_current_url();
	}

	/**
	 * Get the title of the current page
	 *
	 * @access public
	 * @return string
	 */
	public function get_title(): string {
		return $this->driver->get_title();
	}

	/**
	 * Click an element
	 *
	 * @access public
	 * @param string $selector css or xpath selector
	 * @param string|null $within selector of a parent element to search in
	 */
	public function click(string $selector, ?string $within = null): void {
		$this->driver->click($selector, $within);
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
		$this->driver->fill($selector, $value, $within);
	}

	/**
	 * Type a value into an element without clearing it first
	 *
	 * @access public
	 * @param string $selector css or xpath selector
	 * @param string $keys value or key sequence
	 * @param string|null $within selector of a parent element to search in
	 */
	public function send_keys(string $selector, string $keys, ?string $within = null): void {
		$this->driver->send_keys($selector, $keys, $within);
	}

	/**
	 * Clear an element
	 *
	 * @access public
	 * @param string $selector css or xpath selector
	 * @param string|null $within selector of a parent element to search in
	 */
	public function clear(string $selector, ?string $within = null): void {
		$this->driver->clear($selector, $within);
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
		return $this->driver->get_text($selector, $within);
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
		return $this->driver->get_attribute($selector, $attribute, $within);
	}

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
	public function is_displayed(string $selector, ?string $within = null): bool {
		return $this->driver->is_displayed($selector, $within);
	}

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
	public function is_selected(string $selector, ?string $within = null): bool {
		return $this->driver->is_selected($selector, $within);
	}

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
	public function is_enabled(string $selector, ?string $within = null): bool {
		return $this->driver->is_enabled($selector, $within);
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
		return $this->driver->count($selector, $within);
	}

	/**
	 * Hover over an element
	 *
	 * @access public
	 * @param string $selector css or xpath selector
	 * @param string|null $within selector of a parent element to search in
	 */
	public function hover(string $selector, ?string $within = null): void {
		$this->driver->hover($selector, $within);
	}

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
	public function execute_script(string $script, array $arguments = []): mixed {
		return $this->driver->execute_script($script, $arguments);
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
		$this->driver->wait_until_visible($selector, $seconds);
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
		$this->driver->wait_until_clickable($selector, $seconds);
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
		$this->driver->wait_until_present($selector, $seconds);
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
		$this->driver->wait_until_hidden($selector, $seconds);
	}

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
	public function wait_until(callable $predicate, int $seconds = 10): void {
		$this->driver->wait_until($predicate, $seconds);
	}

	/**
	 * Set the implicit timeout for element searches
	 *
	 * Playwright has no implicit timeouts, this is a no-op there.
	 *
	 * @access public
	 * @param int $seconds
	 */
	public function set_implicit_timeout(int $seconds): void {
		$this->driver->set_implicit_timeout($seconds);
	}

	/**
	 * Announce the scene that is about to run, and its driver
	 *
	 * Called by Unit::setUpBeforeClass().
	 *
	 * @access public
	 * @param string $scene scene class
	 * @param string|null $driver_name driver declared by the scene, null falls back to Config::$driver
	 */
	public static function begin_scene(string $scene, ?string $driver_name): void {
		self::$current_scene = $scene;
		self::$scene_driver = $driver_name ?? Config::$driver;
	}

	/**
	 * End the current scene
	 *
	 * Called by Unit::tearDownAfterClass().
	 *
	 * @access public
	 */
	public static function end_scene(): void {
		self::$current_scene = null;
		self::$scene_driver = null;
	}

	/**
	 * Get the driver name of the current scene
	 *
	 * @access public
	 * @return string|null
	 */
	public static function get_scene_driver(): ?string {
		return self::$scene_driver;
	}
}
