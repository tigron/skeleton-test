<?php
/**
 * Skeleton\Test\Selenium\Webdriver class
 *
 * @author Lionel Laffineur <lionel@tigron.be>
 */

namespace Skeleton\Test\Selenium;

use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Skeleton\Test\Config;
use Skeleton\Test\Selenium\Webdriver\Element;

class Webdriver extends \Facebook\WebDriver\Remote\RemoteWebDriver {

	/**
	 * Page object
	 *
	 * @access public
	 * @var Skeleton\Test\Page $page
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
	 * @param WebDriverBy $by
	 * @param int|null $timeout
	 * @return Element
	 */
	public function findElement(WebDriverBy $by, $timeout = null): Element {
		if ($timeout !== null) {
			$this->manage()->timeouts()->implicitlyWait($timeout);
		}

		$src = parent::findElement($by);
		$dest = new Element($this->executeMethod, $this->sessionID);
		$dest->import($src);
		$dest->webdriver = $this;

		if ($timeout !== null) {
			$this->manage()->timeouts()->implicitlyWait(Config::$default_implicit_timeout);
		}

		return $dest;
	}

	/**
	 * Find elements
	 *
	 * @access public
	 * @param WebDriverBy $by
	 * @param int|null $timeout
	 * @return array
	 */
	public function findElements(WebDriverBy $by, $timeout = null): array {
		if ($timeout !== null) {
			$this->manage()->timeouts()->implicitlyWait($timeout);
		}

		$sources = parent::findElements($by);
		$destinations = [];
		foreach ($sources as $source) {
			$dest = new Element($this->executeMethod, $this->sessionID);
			$dest->import($source);
			$dest->webdriver = $this;
			$destinations[] = $dest;
		}

		if ($timeout !== null) {
			$this->manage()->timeouts()->implicitlyWait(Config::$default_implicit_timeout);
		}

		return $destinations;
	}

	/**
	 * Quit the webdriver session
	 *
	 * Closes the current browser session and resets the singleton.
	 * Named quit_all to avoid clashing with the instance method
	 * RemoteWebDriver::quit()
	 *
	 * @access public
	 */
	public static function quit_all(): void {
		if (self::$webdriver !== null) {
			self::$webdriver->quit();
			self::$webdriver = null;
		}
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
				$chrome_options = new \Facebook\WebDriver\Chrome\ChromeOptions();
				$arguments = [
					'--no-sandbox',
					'--disable-gpu',
					'--disable-infobars',
					'--enable-automation',
					'--suppress-message-center-popups',
					'--start-maximized',
					'--test-type',
				];
				$chrome_options->addArguments($arguments);
				$chrome_options->setExperimentalOption('excludeSwitches', [ 'enable-automation' ]);
				$chrome_options->setExperimentalOption('useAutomationExtension', false);
				$prefs = ['profile.default_content_setting_values.notifications' => 2];
				$chrome_options->setExperimentalOption('prefs', $prefs);

				$capabilities = DesiredCapabilities::chrome();
				$capabilities->setCapability(\Facebook\WebDriver\Chrome\ChromeOptions::CAPABILITY, $chrome_options);

				$custom_capability = [
					'args' => $arguments,
					'excludeSwitches' => [ 'enable-automation' ],
					'useAutomationExtension' => false,
				];
				$capabilities->setCapability('goog:chromeOptions', $custom_capability);

				self::$webdriver = self::create(
					Config::$selenium_hub,
					$capabilities,
					60 * 1000, // Connection timeout in miliseconds
					60 * 1000  // Request timeout in miliseconds
				);
			} elseif (Config::$browser === 'firefox') {
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
				$profile->setPreference('browser.helperApps.neverAsk.openFile', implode(',', $mime_types));
				$profile->setPreference('browser.helperApps.neverAsk.saveToDisk', implode(',', $mime_types));
				$capabilities = DesiredCapabilities::firefox();
				$capabilities->setCapability(\Facebook\WebDriver\Firefox\FirefoxDriver::PROFILE, $profile);
				self::$webdriver = self::create(Config::$selenium_hub, $capabilities);
			} else {
				throw new \Exception('Unknown browser \'' . Config::$browser . '\'');
			}
			self::$webdriver->manage()->timeouts()->implicitlyWait(Config::$default_implicit_timeout);
		}

		return self::$webdriver;
	}
}
