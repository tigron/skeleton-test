<?php
/**
 * Playwright Page class
 *
 * @author Gerry Demaret <gerry@tigron.be>
 */

namespace Skeleton\Test\Page;

use Playwright\Browser\Browser;
use Playwright\Page\Page as PlaywrightPage;
use Playwright\PlaywrightFactory;
use Playwright\Configuration\PlaywrightConfigBuilder;

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
				$trace_file = \Skeleton\Test\Config::$playwright_trace_path . '/' . $traced_page['class'] . '-' . self::$trace_counter++ . '.zip';

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
		} else if (\Skeleton\Test\Config::$browser == 'firefox') {
			self::$browser = self::$playwright->firefox()->connect($server);
		} else {
			throw new \Exception("Unknown browser '" . \Skeleton\Test\Config::$browser . "'");
		}

		$contextOptions = [
			'ignoreHTTPSErrors' => true,
		];

		self::$context = self::$browser->newContext($contextOptions);
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
}
