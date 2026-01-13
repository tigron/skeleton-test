<?php
/**
 * Skeleton\Test\RWebdriver class
 *
 * @author Lionel Laffineur <lionel@tigron.be>
 */

namespace Skeleton\Test\Selenium;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Skeleton\Test\Config;

class Webdriver extends \Facebook\WebDriver\Remote\RemoteWebDriver {

	/**
	 * Page object
	 *
	 * @var Skeleton\Test\Page $page
	 * @access public
	 */
	public $page;

	/**
	 * Webdriver
	 *
	 * @access private
	 * @var Webdriver $webdriver
	 */
	private static $webdriver = null;

	/**
	 * Find an element
	 *
	 * @access public
	 * @param Facebook\WebDriver\WebDriverBy $by
	 * @param $timeout
	 * @return Skeleton\Test\Selenium\Webdriver\Element $element
	 */
	public function findElement(WebDriverBy $by, $timeout = null) {
		if ($timeout != null) {
			$this->manage()->timeouts()->implicitlyWait($timeout);
		}
        $src = parent::findElement($by);
		$dest = new \Skeleton\Test\Selenium\Webdriver\Element($this->executeMethod, $this->sessionID);
		$dest->import($src);
		$dest->webdriver = $this;

		if ($timeout != null) {
			$this->manage()->timeouts()->implicitlyWait(Config::$default_implicit_timeout);
		}
		return $dest;
	}

	/**
	 * Find elements
	 *
	 * @access public
	 * @param Facebook\WebDriver\WebDriverBy $by
	 * @param $timeout
	 * @return array $elements
	 */
	public function findElements(WebDriverBy $by, $timeout = null) {
		if ($timeout != null) {
			$this->manage()->timeouts()->implicitlyWait($timeout);
		}
        $sources = parent::findElements($by);
        $destinations = [];
        foreach ($sources as $source) {
			$dest = new \Skeleton\Test\Selenium\Webdriver\Element($this->executeMethod, $this->sessionID);
			$dest->import($source);
			$dest->webdriver = $this;
			$destinations[] = $dest;
        }

		if ($timeout != null) {
			$this->manage()->timeouts()->implicitlyWait(Config::$default_implicit_timeout);
		}
		return $destinations;
	}

	/**
	 * Create an instance of webdriver based on Config
	 *
	 * @access public
	 * @return self $webdriver
	 */
	public static function initiate(): self {
		if (self::$webdriver === null) {
			if (Config::$browser === 'chrome') {
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


				$driver = self::create(
					Config::$selenium_hub,
					$capabilities,
					60 * 1000, // Connection timeout in miliseconds
					60 * 1000  // Request timeout in miliseconds
				);
				self::$webdriver = $driver;
			} else if (Config::$browser === 'firefox') {
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
				$driver = self::create(Config::$selenium_hub, $capabilities);
				self::$webdriver = $driver;
			} else {
				throw new \Exception("Unknown browser '" . Config::$browser . "'");
			}
			self::$webdriver->manage()->timeouts()->implicitlyWait(5);
		}

		return self::$webdriver;
	}
}
