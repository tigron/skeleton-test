<?php
/**
 * Skeleton\Test\Unit class
 *
 * Base class for scenes. A scene declares the driver its pages run on
 * (selenium or playwright); the driver is inherited by every page the
 * scene instantiates. Scenes without an explicit driver use
 * Config::$driver.
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author Lionel Laffineur <lionel@tigron.be>
 */

namespace Skeleton\Test;

class Unit extends \PHPUnit\Framework\TestCase {

	/**
	 * Driver used by the pages of this scene: selenium or playwright.
	 * Defaults to Config::$driver when not set.
	 *
	 * @access protected
	 * @var string|null $driver
	 */
	protected static $driver = null;

	/**
	 * setupBeforeScene
	 * function called before a scene to init resources used in the scene
	 *
	 * @access public
	 */
	public static function setupBeforeScene() {
	}

	/**
	 * This method is called before the first case in the test.
	 *
	 * @access public
	 */
	public static function setUpBeforeClass(): void {
		$class = get_called_class();

		\Skeleton\Test\Page::begin_scene($class, static::$driver);

		if (isset(Config::$start_timestamp_filename)) {
			if (!file_exists(Config::$start_timestamp_filename)) {
				throw new Timingfilenotfound('Timing file ' . Config::$start_timestamp_filename . ' was not found.');
			}
			$timestamp = round(time() - intval(file_get_contents(Config::$start_timestamp_filename)));
			$hours = floor($timestamp / 3600);
			$minutes = floor(($timestamp % 3600) / 60);
			$seconds = $timestamp % 60;
			$time = sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
			$timings = [];
			if (file_exists(Config::$timings_filename)) {
				$timings = json_decode(file_get_contents(Config::$timings_filename), true);
			}
			$data = [];
			if (isset($timings[$class])) {
				$data = $timings[$class];
			}
			$data['start_timestamp'] = $timestamp;
			$data['start_time'] = $time;
			$timings[$class] = $data;
			file_put_contents(Config::$timings_filename, json_encode($timings, JSON_PRETTY_PRINT));
		}

		try {
			$class::setupBeforeScene();
		} catch (\Exception $e) {
			printf("Error in %s::setupBeforeScene(): %s\n%s\n", $class, $e->getMessage(), $e->getTraceAsString());
		}
	}

	/**
	 * tearDownAfterScene
	 * function called after a scene to clean resources used during the scene
	 *
	 * @access public
	 */
	public static function tearDownAfterScene() {
	}

	/**
	 * This method is called after the last case in the test has been run.
	 * We can safely terminate the webdriver connection here.
	 *
	 * @access public
	 */
	public static function tearDownAfterClass(): void {
		$class = get_called_class();

		if (isset(Config::$start_timestamp_filename)) {
			if (!file_exists(Config::$start_timestamp_filename)) {
				throw new Timingfilenotfound('Timing file ' . Config::$start_timestamp_filename . ' was not found.');
			}
			$timestamp = round(time() - intval(file_get_contents(Config::$start_timestamp_filename)));
			$hours = floor($timestamp / 3600);
			$minutes = floor(($timestamp % 3600) / 60);
			$seconds = $timestamp % 60;
			$time = sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
			$timings = [];
			if (file_exists(Config::$timings_filename)) {
				$timings = json_decode(file_get_contents(Config::$timings_filename), true);
			}
			$data = [];
			if (isset($timings[$class])) {
				$data = $timings[$class];
			}
			$data['stop_timestamp'] = $timestamp;
			$data['stop_time'] = $time;
			$timings[$class] = $data;
			file_put_contents(Config::$timings_filename, json_encode($timings, JSON_PRETTY_PRINT));
		}

		try {
			$class::tearDownAfterScene();
		} catch (\Exception $e) {
			printf("Error in %s::tearDownAfterScene(): %s\n%s\n", $class, $e->getMessage(), $e->getTraceAsString());
		}

		\Skeleton\Test\Driver\Selenium::quit_all();
		\Skeleton\Test\Driver\Playwright::close_all();
		\Skeleton\Test\Page::end_scene();
	}

	/**
	 * Catch calls to the "webdriver" property and return the webdriver session
	 *
	 * Transitional legacy access for scenes and helper traits that are not
	 * ported to the Driver API yet. Only available on scenes running on
	 * selenium; port this logic into page objects instead of using it.
	 *
	 * @access public
	 * @param string $key
	 * @return mixed
	 */
	public function __get($key) {
		if ($key === 'webdriver') {
			if (\Skeleton\Test\Page::get_scene_driver() !== 'selenium') {
				throw new \Exception('Scene ' . static::class . ' does not run on selenium, $this->webdriver is not available');
			}

			return \Skeleton\Test\Selenium\Webdriver::initiate();
		}
	}
}
