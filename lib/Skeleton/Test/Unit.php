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
			if (Page::get_scene_driver() !== 'selenium') {
				throw new \Exception('Scene ' . static::class . ' does not run on selenium, $this->webdriver is not available');
			}

			return \Skeleton\Test\Selenium\Webdriver::initiate();
		}

		return null;
	}

	/**
	 * setupBeforeScene
	 * function called before a scene to init resources used in the scene
	 *
	 * @access public
	 */
	public static function setupBeforeScene(): void {
	}

	/**
	 * This method is called before the first case in the test.
	 *
	 * @access public
	 */
	public static function setUpBeforeClass(): void {
		$class = get_called_class();

		Page::begin_scene($class, static::$driver);

		self::record_timing('start');

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
	public static function tearDownAfterScene(): void {
	}

	/**
	 * This method is called after the last case in the test has been run.
	 * We can safely terminate the webdriver connection here.
	 *
	 * @access public
	 */
	public static function tearDownAfterClass(): void {
		$class = get_called_class();

		self::record_timing('stop');

		try {
			$class::tearDownAfterScene();
		} catch (\Exception $e) {
			printf("Error in %s::tearDownAfterScene(): %s\n%s\n", $class, $e->getMessage(), $e->getTraceAsString());
		}

		\Skeleton\Test\Driver\Selenium::quit_all();
		\Skeleton\Test\Driver\Playwright::close_all();
		Page::end_scene();
	}

	/**
	 * Record a timing entry for the current scene
	 *
	 * The start timestamp is read from Config::$start_timestamp_filename,
	 * the result is written to Config::$timings_filename as json.
	 *
	 * @access private
	 * @param string $event 'start' or 'stop'
	 * @throws Timingfilenotfound when the start timestamp file is missing
	 */
	private static function record_timing(string $event): void {
		if (Config::$start_timestamp_filename === null || Config::$timings_filename === null) {
			return;
		}

		if (!file_exists(Config::$start_timestamp_filename)) {
			if ($event !== 'start') {
				throw new Timingfilenotfound('Timing file ' . Config::$start_timestamp_filename . ' was not found.');
			}

			// The first scene of the run writes the start timestamp itself: it
			// marks the beginning of the timeline that the selenium video
			// recording is aligned to
			file_put_contents(Config::$start_timestamp_filename, strval(time()));
		}

		$class = get_called_class();

		$timestamp = round(time() - intval(file_get_contents(Config::$start_timestamp_filename)));
		$hours = floor($timestamp / 3600);
		$minutes = floor(($timestamp % 3600) / 60);
		$seconds = $timestamp % 60;
		$time = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);

		$timings = [];
		if (is_file(Config::$timings_filename)) {
			$decoded = json_decode(file_get_contents(Config::$timings_filename), true);
			if (is_array($decoded)) {
				$timings = $decoded;
			}
		}

		$data = $timings[$class] ?? [];
		$data[$event . '_timestamp'] = $timestamp;
		$data[$event . '_time'] = $time;
		$timings[$class] = $data;

		file_put_contents(Config::$timings_filename, json_encode($timings, JSON_PRETTY_PRINT));
	}
}
