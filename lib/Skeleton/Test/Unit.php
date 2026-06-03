<?php
/**
 * Skeleton\Test\Unit\Base abstract class
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author Lionel Laffineur <lionel@tigron.be>
 */

namespace Skeleton\Test;

abstract class Unit extends \PHPUnit\Framework\TestCase {

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

		if (isset(\Skeleton\Test\Config::$start_timestamp_filename)) {
			if (!file_exists(\Skeleton\Test\Config::$start_timestamp_filename)) {
				throw new \Skeleton\Test\Timingfilenotfound('Timing file ' . \Skeleton\Test\Config::$start_timestamp_filename . ' was not found.');
			}

			$timestamp = round(time() - intval(file_get_contents(\Skeleton\Test\Config::$start_timestamp_filename)));
			$hours = floor($timestamp / 3600);
			$minutes = floor(($timestamp % 3600) / 60);
			$seconds = $timestamp % 60;
			$time = sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
			$timings = [];

			if (file_exists(\Skeleton\Test\Config::$timings_filename)) {
				$timings = json_decode(file_get_contents(\Skeleton\Test\Config::$timings_filename), true);
			}

			$data = [];
			if (isset($timings[$class])) {
				$data = $timings[$class];
			}

			$data['start_timestamp'] = $timestamp;
			$data['start_time'] = $time;
			$timings[$class] = $data;

			file_put_contents(\Skeleton\Test\Config::$timings_filename, json_encode($timings, JSON_PRETTY_PRINT));
		}

		try {
			$class::setupBeforeScene();
		} catch(\Exception $e) {
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
	 *
	 * @access public
	 */
	public static function tearDownAfterClass(): void {
		$class = get_called_class();

		if (isset(\Skeleton\Test\Config::$start_timestamp_filename)) {
			if (!file_exists(\Skeleton\Test\Config::$start_timestamp_filename)) {
				throw new \Skeleton\Test\Timingfilenotfound('Timing file ' . \Skeleton\Test\Config::$start_timestamp_filename . ' was not found.');
			}

			$timestamp = round(time() - intval(file_get_contents(\Skeleton\Test\Config::$start_timestamp_filename)));
			$hours = floor($timestamp / 3600);
			$minutes = floor(($timestamp % 3600) / 60);
			$seconds = $timestamp % 60;
			$time = sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
			$timings = [];

			if (file_exists(\Skeleton\Test\Config::$timings_filename)) {
				$timings = json_decode(file_get_contents(\Skeleton\Test\Config::$timings_filename), true);
			}

			$data = [];
			if (isset($timings[$class])) {
				$data = $timings[$class];
			}

			$data['stop_timestamp'] = $timestamp;
			$data['stop_time'] = $time;
			$timings[$class] = $data;

			file_put_contents(\Skeleton\Test\Config::$timings_filename, json_encode($timings, JSON_PRETTY_PRINT));
		}

		try {
			$class::tearDownAfterScene();
		} catch (\Exception $e) {
			printf("Error in %s::tearDownAfterScene(): %s\n%s\n", $class, $e->getMessage(), $e->getTraceAsString());
		}
	}
}
