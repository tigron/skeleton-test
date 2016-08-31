<?php
/**
 * Page class
 *
 * @author Gerry Demaret <gerry@tigron.be>
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

namespace Skeleton\Test;

abstract class Page {

	/**
	 * Webdriver variable
	 *
	 * @access protected
	 * @var \Facebook\Webdriver\Webdriver $webdriver
	 */
	protected $webdriver = null;

	/**
	 * Construct
	 *
	 * @access public
	 * @param \Facebook\Webdriver\WebDriver
	 */
	public function __construct(\Facebook\Webdriver\Webdriver $webdriver) {
		$this->webdriver = $webdriver;
	}

	/**
	 * Get url
	 *
	 * @access public
	 * @return string $url
	 */
	abstract public function get_url();

	/**
	 * Open the page
	 *
	 * @access public
	 */
	public function open() {
		$this->webdriver->get($this->get_url());
	}

	/**
	 * Has error
	 * Checks if the current page contains an error
	 *
	 * @access public
	 * @param string $error Optional string which will contain the error message if there was any
	 * @return bool
	 */
	public function has_error(&$error = '') {
		$error_messages = [
			'/<b>Fatal error<\/b>: (.*)/',
			'/<b>Parse error<\/b>: (.*)/',
			'/<b>Warning<\/b>: (.*)/',
			'/<b>Notice<\/b>: (.*)/'
		];

		$html = $this->webdriver->getPageSource();
		foreach ($error_messages as $error_message) {
			preg_match($error_message, $html, $output_array);
			if (count($output_array) > 0) {
				$error = trim(strip_tags($output_array[0]));
				return true;
			}
		}

		if (class_exists('Skeleton\Error\Detector')) {
			return \Skeleton\Error\Detector::detect($html, $error);
		}

		return false;
	}
}
