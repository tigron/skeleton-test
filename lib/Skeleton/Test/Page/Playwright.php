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
		}
	}

	/**
	 * Close open handles
	 */
	public static function close(): void {
		if (\Skeleton\Test\Config::$playwright_trace_path !== null) {
			$trace_file = \Skeleton\Test\Config::$playwright_trace_path . '/' . get_called_class() . '.zip';
			// TODO keep page somewhere
			// self::$context->stopTracing(self::$page, $trace_file);
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
}
