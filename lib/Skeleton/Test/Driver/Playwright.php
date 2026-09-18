<?php
/**
 * Playwright Driver implementation
 *
 * Wraps a Playwright page and implements the engine-agnostic Driver API on
 * top of playwright-php. One browser and context are shared per process,
 * every driver instance gets its own page. Element operations always use
 * the first match, mirroring the selenium findElement semantics.
 *
 * @author Gerry Demaret <gerry@tigron.be>
 */

namespace Skeleton\Test\Driver;

use Playwright\Browser\Browser;
use Playwright\Browser\BrowserContext;
use Playwright\Configuration\PlaywrightConfigBuilder;
use Playwright\Exception\TimeoutException;
use Playwright\Locator\Locator;
use Playwright\Page\Page as PlaywrightPage;
use Playwright\PlaywrightFactory;
use Skeleton\Test\Config;
use Skeleton\Test\Page\Elementnotfound;

class Playwright implements \Skeleton\Test\Driver {

	/**
	 * Local playwright instance
	 *
	 * @access private
	 * @var object $playwright
	 */
	private static $playwright = null;

	/**
	 * Browser
	 *
	 * @access private
	 * @var Browser|null $browser
	 */
	private static ?Browser $browser = null;

	/**
	 * BrowserContext
	 *
	 * @access private
	 * @var BrowserContext|null $context
	 */
	private static ?BrowserContext $context = null;

	/**
	 * Pages with tracing enabled, waiting to be written to a trace file
	 *
	 * @access private
	 * @var array $traced_pages
	 */
	private static array $traced_pages = [];

	/**
	 * Counter for unique trace filenames
	 *
	 * @access private
	 * @var int $trace_counter
	 */
	private static int $trace_counter = 0;

	/**
	 * Playwright page
	 *
	 * @access private
	 * @var PlaywrightPage $page
	 */
	private PlaywrightPage $page;

	/**
	 * Constructor
	 *
	 * @access public
	 * @param string $page_class page class using this driver
	 * @param string $scene scene class using this driver
	 */
	public function __construct(string $page_class, string $scene) {
		if (self::$browser === null) {
			self::initialize();
		}

		$this->page = self::$context->newPage();

		if (Config::$playwright_trace_path !== null) {
			self::$context->startTracing($this->page, [
				'screenshots' => true,
				'snapshots' => true,
			]);

			self::$traced_pages[] = [
				'scene' => $scene,
				'page_class' => $page_class,
				'page' => $this->page,
			];
		}
	}

	/**
	 * Write the trace files and tear down the browser
	 *
	 * Stops tracing for all traced pages and closes the shared browser.
	 *
	 * @access public
	 */
	public static function close_all(): void {
		if (Config::$playwright_trace_path !== null && self::$context !== null) {
			if (!is_dir(Config::$playwright_trace_path)) {
				mkdir(Config::$playwright_trace_path, 0777, true);
			}

			foreach (self::$traced_pages as $traced_page) {
				$trace_file = Config::$playwright_trace_path . '/' . $traced_page['scene'] . '-' . $traced_page['page_class'] . '-' . self::$trace_counter . '.zip';
				self::$trace_counter++;

				try {
					self::$context->stopTracing($traced_page['page'], $trace_file);
				} catch (\Exception $e) {
					error_log('Could not write trace ' . $trace_file . ': ' . $e->getMessage());
				}
			}
		}

		self::$traced_pages = [];

		if (self::$context !== null) {
			self::$context->close();
			self::$context = null;
		}

		if (self::$browser !== null) {
			self::$browser->close();
			self::$browser = null;
		}

		if (self::$playwright !== null) {
			self::$playwright->close();
			self::$playwright = null;
		}
	}

	/**
	 * Point playwright to the fake node and server
	 *
	 * @access private
	 */
	private static function initialize(): void {
		if (Config::$node_path !== null) {
			putenv('PLAYWRIGHT_NODE_PATH=' . Config::$node_path);
		}

		$builder = PlaywrightConfigBuilder::create();
		$config = $builder->build();

		self::$playwright = PlaywrightFactory::create($config);

		$server = Config::$playwright_server;

		if (in_array(Config::$browser, ['chrome', 'chromium'])) {
			self::$browser = self::$playwright->chromium()->connect($server);
		} else if (Config::$browser === 'firefox') {
			self::$browser = self::$playwright->firefox()->connect($server);
		} else {
			throw new \Exception("Unknown browser '" . Config::$browser . "'");
		}

		$contextOptions = [
			'ignoreHTTPSErrors' => true,
		];

		self::$context = self::$browser->newContext($contextOptions);
	}

	/**
	 * Locate a selector, optionally scoped to a parent element
	 *
	 * @access private
	 * @param string $selector css or xpath selector
	 * @param string|null $within selector of a parent element to search in
	 * @return Locator
	 */
	private function locate(string $selector, ?string $within = null): Locator {
		$locator = $this->page->locator($within ?? $selector);

		if ($within !== null) {
			$locator = $locator->first()->locator($selector);
		}

		return $locator;
	}

	/**
	 * Locate the first match of a selector
	 *
	 * @access private
	 * @param string $selector css or xpath selector
	 * @param string|null $within selector of a parent element to search in
	 * @return Locator
	 */
	private function first(string $selector, ?string $within = null): Locator {
		return $this->locate($selector, $within)->first();
	}

	/**
	 * Open an URL
	 *
	 * @access public
	 * @param string $url
	 */
	public function open_url(string $url): void {
		$this->page->goto($url);
	}

	/**
	 * Refresh the current page
	 *
	 * @access public
	 */
	public function refresh(): void {
		$this->page->reload();
	}

	/**
	 * Get the URL of the current page
	 *
	 * @access public
	 * @return string
	 */
	public function get_current_url(): string {
		return $this->page->url();
	}

	/**
	 * Get the title of the current page
	 *
	 * @access public
	 * @return string
	 */
	public function get_title(): string {
		return $this->page->title();
	}

	/**
	 * Click an element
	 *
	 * @access public
	 * @param string $selector css or xpath selector
	 * @param string|null $within selector of a parent element to search in
	 */
	public function click(string $selector, ?string $within = null): void {
		$this->first($selector, $within)->click();
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
		$this->first($selector, $within)->fill($value);
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
		$this->first($selector, $within)->type($keys);
	}

	/**
	 * Clear an element
	 *
	 * @access public
	 * @param string $selector css or xpath selector
	 * @param string|null $within selector of a parent element to search in
	 */
	public function clear(string $selector, ?string $within = null): void {
		$this->first($selector, $within)->clear();
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
		return $this->first($selector, $within)->innerText();
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
		return $this->first($selector, $within)->getAttribute($attribute);
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
		return $this->first($selector, $within)->isVisible();
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
		$selected = $this->first($selector, $within)->evaluate('el => el.selected');

		if ($selected === null) {
			return false;
		}

		return (bool)$selected;
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
		return $this->first($selector, $within)->isEnabled();
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
		return $this->locate($selector, $within)->count();
	}

	/**
	 * Hover over an element
	 *
	 * @access public
	 * @param string $selector css or xpath selector
	 * @param string|null $within selector of a parent element to search in
	 */
	public function hover(string $selector, ?string $within = null): void {
		$this->first($selector, $within)->hover();
	}

	/**
	 * Execute javascript in the browser
	 *
	 * The script is given in body style (as in selenium executeScript), it
	 * is wrapped in a function for playwright. The arguments are available
	 * in the script as arguments[0], arguments[1], ...
	 *
	 * @access public
	 * @param string $script
	 * @param array $arguments
	 * @return mixed
	 */
	public function execute_script(string $script, array $arguments = []): mixed {
		return $this->page->evaluate('(arguments) => { ' . $script . ' }', $arguments);
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
		try {
			$error_text = $this->page->evaluate("
				() => {
					if (document.querySelector('.exc-message') !== null) {
						return document.querySelector('#plain-exception').innerText;
					}
					return false;
				}
			");

			if ($error_text === false || $error_text === null || $error_text === '') {
				return false;
			}

			$error = $error_text;

			return true;
		} catch (\Exception $e) {
			return false;
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
	 * Wait until the element is visible
	 *
	 * @access public
	 * @param string $selector css or xpath selector
	 * @param int $seconds
	 * @throws Elementnotfound when the element is not visible in time
	 */
	public function wait_until_visible(string $selector, int $seconds = 10): void {
		try {
			$this->first($selector)->waitForVisible([ 'timeout' => $seconds * 1000 ]);
		} catch (TimeoutException $e) {
			throw new Elementnotfound('Element not visible within ' . $seconds . ' seconds: ' . $selector);
		}
	}

	/**
	 * Wait until the element is clickable
	 *
	 * Playwright does not have a distinct clickable state, this waits for
	 * visibility. Clicking auto-waits for actionability.
	 *
	 * @access public
	 * @param string $selector css or xpath selector
	 * @param int $seconds
	 * @throws Elementnotfound when the element is not clickable in time
	 */
	public function wait_until_clickable(string $selector, int $seconds = 10): void {
		$this->wait_until_visible($selector, $seconds);
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
		try {
			$this->first($selector)->waitForAttached([ 'timeout' => $seconds * 1000 ]);
		} catch (TimeoutException $e) {
			throw new Elementnotfound('Element not present within ' . $seconds . ' seconds: ' . $selector);
		}
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
		try {
			$this->first($selector)->waitForHidden([ 'timeout' => $seconds * 1000 ]);
		} catch (TimeoutException $e) {
			throw new Elementnotfound('Element still visible within ' . $seconds . ' seconds: ' . $selector);
		}
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
	 * Playwright has no implicit timeouts, this is a no-op.
	 *
	 * @access public
	 * @param int $seconds
	 */
	public function set_implicit_timeout(int $seconds): void {
	}

	/**
	 * Get the native driver object
	 *
	 * @access public
	 * @return PlaywrightPage
	 */
	public function native(): mixed {
		return $this->page;
	}
}
