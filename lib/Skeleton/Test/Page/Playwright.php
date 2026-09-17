<?php
/**
 * Playwright Page class
 *
 * @author Gerry Demaret <gerry@tigron.be>
 */

namespace Skeleton\Test\Page;

use Playwright\Browser\Browser;
use Playwright\Configuration\PlaywrightConfigBuilder;
use Playwright\Exception\TimeoutException;
use Playwright\Page\Page as PlaywrightPage;
use Playwright\PlaywrightFactory;

abstract class Playwright extends \Skeleton\Test\Page {

	/**
	 * playright browser
	 *
	 * @access protected
	 * @var Browser $browser
	 */
	protected static $browser = null;

	/**
	 * page
	 *
	 * @access protected
	 * @var PlaywrightPage $page
	 */
	protected $page = null;

	/**
	 * local playwright instance
	 *
	 * @access private
	 * @var object $playwright
	 */
	protected static $playwright = null;

	/**
	 * BrowserContext
	 *
	 * @access protected
	 * @var \Playwright\BrowserContext $context
	 */
	protected static $context = null;

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
	 * Constructor
	 *
	 * @access public
	 */
	public function __construct() {
		if (self::$browser === null) {
			self::initialize_playwright();
		}

		$this->page = self::$context->newPage();

		if (\Skeleton\Test\Config::$playwright_trace_path !== null) {
			self::$context->startTracing($this->page, [
				'screenshots' => true,
				'snapshots' => true,
			]);

			self::$traced_pages[] = [
				'class' => get_class($this),
				'page' => $this->page,
			];
		}
	}

	/**
	 * Open page
	 *
	 * @access public
	 */
	public function open(): void {
		$this->page->goto($this->get_url());
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
	 * Find an element on the page
	 *
	 * @access public
	 * @param string $selector css or xpath selector
	 * @param int $timeout seconds to wait for the element to be attached, 0 = immediate
	 * @return \Skeleton\Test\Page\Element
	 * @throws \Skeleton\Test\Page\Elementnotfound when the element is not present
	 */
	public function find_element(string $selector, int $timeout = 0): \Skeleton\Test\Page\Element {
		$element = $this->page->locator($selector);

		if ($timeout === 0) {
			if ($element->count() === 0) {
				throw new \Skeleton\Test\Page\Elementnotfound('Element not found: ' . $selector);
			}
		} else {
			try {
				$element->waitForAttached([ 'timeout' => $timeout * 1000 ]);
			} catch (TimeoutException $e) {
				throw new \Skeleton\Test\Page\Elementnotfound('Element not found: ' . $selector);
			}
		}

		return new \Skeleton\Test\Page\Playwright\Element($element);
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
		$elements = [];

		foreach ($this->page->locator($selector)->all() as $locator) {
			$elements[] = new \Skeleton\Test\Page\Playwright\Element($locator);
		}

		return $elements;
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
			$this->page->locator($selector)->waitForVisible([ 'timeout' => $seconds * 1000 ]);
		} catch (TimeoutException $e) {
			throw new \Skeleton\Test\Page\Elementnotfound('Element not visible: ' . $selector);
		}

		return $this->find_element($selector);
	}

	/**
	 * Wait until the element is clickable
	 *
	 * Playwright does not have a distinct clickable state, this waits for visibility.
	 *
	 * @access public
	 * @param string $selector css or xpath selector
	 * @param int $seconds
	 * @return \Skeleton\Test\Page\Element
	 * @throws \Skeleton\Test\Page\Elementnotfound when the element is not clickable in time
	 */
	public function wait_until_clickable(string $selector, int $seconds = 10): \Skeleton\Test\Page\Element {
		return $this->wait_until_visible($selector, $seconds);
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
			$this->page->locator($selector)->waitForAttached([ 'timeout' => $seconds * 1000 ]);
		} catch (TimeoutException $e) {
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
			$this->page->locator($selector)->waitForHidden([ 'timeout' => $seconds * 1000 ]);
		} catch (TimeoutException $e) {
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
		return $this->page->url();
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
	 * Close open handles
	 *
	 * Stops tracing for all traced pages and tears down the browser.
	 *
	 * @access public
	 */
	public static function close(): void {
		if (\Skeleton\Test\Config::$playwright_trace_path !== null && self::$context !== null) {
			if (!is_dir(\Skeleton\Test\Config::$playwright_trace_path)) {
				mkdir(\Skeleton\Test\Config::$playwright_trace_path, 0777, true);
			}

			foreach (self::$traced_pages as $traced_page) {
				$trace_file = \Skeleton\Test\Config::$playwright_trace_path . '/' . $traced_page['class'] . '-' . self::$trace_counter . '.zip';
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
	private static function initialize_playwright(): void {
		if (\Skeleton\Test\Config::$node_path !== null) {
			putenv('PLAYWRIGHT_NODE_PATH=' . \Skeleton\Test\Config::$node_path);
		}

		$builder = PlaywrightConfigBuilder::create();
		$config = $builder->build();

		self::$playwright = PlaywrightFactory::create($config);

		$server = \Skeleton\Test\Config::$playwright_server;

		if (in_array(\Skeleton\Test\Config::$browser, ['chrome', 'chromium'])) {
			self::$browser = self::$playwright->chromium()->connect($server);
		} else if (\Skeleton\Test\Config::$browser === 'firefox') {
			self::$browser = self::$playwright->firefox()->connect($server);
		} else {
			throw new \Exception("Unknown browser '" . \Skeleton\Test\Config::$browser . "'");
		}

		$contextOptions = [
			'ignoreHTTPSErrors' => true,
		];

		self::$context = self::$browser->newContext($contextOptions);
	}
}
