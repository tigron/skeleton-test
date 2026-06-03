<?php
/**
 * Playwright class
 */

namespace Skeleton\Test\Unit;

use Playwright\PlaywrightFactory;
use Playwright\Configuration\PlaywrightConfigBuilder;

class Playwright extends \Skeleton\Test\Unit {

	/**
	 * local playwright instance
	 *
	 * @access private
	 * @var object $playwright
	 */
	private static $playwright = null;

	/**
	 * Browser
	 *
	 * @access private
	 * @var \Playwright\Browser $browser
	 */
	private static $browser = null;

	/**
	 * BrowserContext
	 *
	 * @access private
	 * @var \Playwright\BrowserContext $context
	 */
	private static $context = null;

	/**
	 * Page
	 *
	 * @access private
	 * @var \Playwright\Page $page
	 */
	private static $page = null;


	/**
	 * browser and page need to be set up to be accessible
	 *
	 * @access public
	 * @param string $key
	 * @return mixed
	 */
	public function __get($key) {
		if ($key === 'browser') {
			return self::get_playwright_browser();
		}

		if ($key === 'page') {
			return self::get_playwright_page();
		}
	}

	/**
	 * set up stuff before getting the browser
	 *
	 * @access public
	 * @return \Playwright\Browser
	 */
	public static function get_playwright_browser() {
		if (self::$browser === null) {
			self::initialize_playwright();
		}

		return self::$browser;
	}

	/**
	 * get page instance
	 *
	 * @access public
	 * @return \Playwright\Page
	 */
	public static function get_playwright_page() {
		if (self::$page === null) {
			self::initialize_playwright();
		}

		return self::$page;
	}


	/**
	 * Point playwright to the fake node and server
	 *
	 * @access private
	 */
	private static function initialize_playwright() {
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
		self::$page = self::$context->newPage();
	}

	/**
	 * Clean up whatever is left over
	 * sometimes objects seem to get cleaned up (or maybe not set up properly)
	 * so check if it has been set at all first!
	 *
	 * @access private
	 */
	private static function close_playwright() {
		if (self::$page !== null) {
			self::$page->close();
			self::$page = null;
		}

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
	 * This is called before the scene is started
	 *
	 * @access public
	 */
	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();

		if (\Skeleton\Test\Config::$playwright_trace_path !== null) {
			self::get_playwright_page();

			self::$context->startTracing(self::$page, [
				'screenshots' => true,
				'snapshots' => true,
			]);
		}
	}

	/**
	 * This method is called after the last test in the scene is done
	 *
	 * @access public
	 */
	public static function tearDownAfterClass(): void {
		if (\Skeleton\Test\Config::$playwright_trace_path !== null) {
			$trace_file = \Skeleton\Test\Config::$playwright_trace_path . '/' . get_called_class() . '.zip';
			self::$context->stopTracing(self::$page, $trace_file);
		}

		parent::tearDownAfterClass();
		self::close_playwright();
	}
}
