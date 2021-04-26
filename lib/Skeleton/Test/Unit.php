<?php
/**
 * Skeleton\Test\Unit class
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author Lionel Laffineur <lionel@tigron.be>
 */

namespace Skeleton\Test;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;

class Unit extends \PHPUnit\Framework\TestCase {

	/**
	 * The webdriver variable
	 *
	 * @access public
	 * @var Facebook\WebDriver\Remote\RemoteWebDriver $webdriver
	 */
	private static $my_webdriver = null;

	/**
	 * Catch calls to the "webdriver" property and proxy them to our
	 * get_webdriver() method.
	 *
	 * @access public
	 * @param string $key
	 * @return mixed
	 */
	public function __get($key) {
		if ($key === 'webdriver') {
			return self::get_webdriver();
		}
	}

	/**
	 * Initialize a new webdriver connection if needed
	 *
	 * @access public
	 */
	public static function get_webdriver() {
		if (self::$my_webdriver === null) {
			if (Config::$browser == 'chrome') {
				$chromeOptions = new \Facebook\WebDriver\Chrome\ChromeOptions();
				$arguments = [
	//				'--headless',
					'--no-sandbox',
					'--disable-gpu',
					'--disable-infobars',
					'--enable-automation',
					'--suppress-message-center-popups',
					'--start-maximized',
					'--test-type',
				];
				$chromeOptions->addArguments($arguments);
				$chromeOptions->setExperimentalOption('excludeSwitches', ['enable-automation']);
				$chromeOptions->setExperimentalOption('useAutomationExtension', false);
				$prefs = ["profile.default_content_setting_values.notifications" => 2];
				$chromeOptions->setExperimentalOption("prefs", $prefs);

				$capabilities = DesiredCapabilities::chrome();
				$capabilities->setCapability(\Facebook\WebDriver\Chrome\ChromeOptions::CAPABILITY, $chromeOptions);

				// this are the lines of code you need to add
				$custom_capability = [
					'args' => $arguments,
					'excludeSwitches' => [ 'enable-automation' ],
					'useAutomationExtension' => false,
				];
				$capabilities->setCapability('goog:chromeOptions', $custom_capability);


				$driver = \Skeleton\Test\Selenium\Webdriver::create(
					Config::$selenium_hub,
					$capabilities,
					60 * 1000, // Connection timeout in miliseconds
					60 * 1000  // Request timeout in miliseconds
				);
				self::$my_webdriver = $driver;
				self::$my_webdriver->manage()->timeouts()->implicitlyWait(5);
			} else if (Config::$browser == 'firefox') {
				$mime_types = [
					'application/binary',
					'application/csv',
					'application/download',
					'application/json',
					'application/octet-stream',
					'application/pdf',
					'application/vnd.ms-excel',
					'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
					'application/vnd.openxmlformats-officedocument.presentationml.presentation',
					'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
					'application/x-unknown',
					'application/zip',
					'binary/octet-stream',
					'image/jpg',
					'image/jpeg',
					'image/gif',
					'image/png',
					'text/plain',
					'text/csv',
					'text/comma-separated-values',
					'text/html',
				];
				$profile = new \Facebook\WebDriver\Firefox\FirefoxProfile();
				$profile->setPreference('browser.startup.homepage', 'about:blank');
				$profile->setPreference('browser.download.folderList', 0);
				$profile->setPreference('browser.download.manager.showWhenStarting', false);
				$profile->setPreference('browser.download.dir', '/tmp');
				$profile->setPreference('browser.helperApps.alwaysAsk.force', false);
				$profile->setPreference('browser.download.manager.alertOnEXEOpen', false);
				$profile->setPreference('browser.download.manager.focusWhenStarting', false);
				$profile->setPreference('browser.download.manager.useWindow', false);
				$profile->setPreference('browser.download.manager.showAlertOnComplete', false);
				$profile->setPreference('browser.download.manager.closeWhenDone', true);
				$profile->setPreference('browser.download.panel.shown', false);
				$profile->setPreference('pdfjs.disabled', true);
				$profile->setPreference('browser.helperApps.neverAsk.openFile', implode($mime_types));
				$profile->setPreference('browser.helperApps.neverAsk.saveToDisk', implode($mime_types));
				$capabilities = DesiredCapabilities::firefox();
				$capabilities->setCapability(\Facebook\WebDriver\Firefox\FirefoxDriver::PROFILE, $profile);
				$driver = \Skeleton\Test\Selenium\Webdriver::create(Config::$selenium_hub, $capabilities);
				self::$my_webdriver = $driver;
				self::$my_webdriver->manage()->timeouts()->implicitlyWait(5);
			} else {
				throw new \Exception("Unknown browser '" . Config::$browser . "'");
			}
		}
		return self::$my_webdriver;
	}

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

		if (isset(Config::$start_timestamp_filename)) {
			if (!file_exists(Config::$start_timestamp_filename)) {
				throw new Timingfilenotfound('Timing file ' . Config::$start_timestamp_filename . ' was not found.');
			}
			$timestamp = round(time() - intval(file_get_contents(Config::$start_timestamp_filename)));
			$time = sprintf("%02d:%02d:%02d", ($timestamp/3600), ($timestamp/60%60), $timestamp%60);
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
		} catch(\Exception $e) {
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
	public static function tearDownAfterClass():void {
		$class = get_called_class();

		if (isset(Config::$start_timestamp_filename)) {
			if (!file_exists(Config::$start_timestamp_filename)) {
				throw new Timingfilenotfound('Timing file ' . Config::$start_timestamp_filename . ' was not found.');
			}
			$timestamp = round(time() - intval(file_get_contents(Config::$start_timestamp_filename)));
			$time = sprintf("%02d:%02d:%02d", ($timestamp/3600), ($timestamp/60%60), $timestamp%60);
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
		}

		if (self::$my_webdriver !== null) {
			self::$my_webdriver->quit();
			self::$my_webdriver = null;
		}
	}
}
