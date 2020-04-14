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
		$this->webdriver->page = $this;
		$this->webdriver->manage()->window()->maximize();
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
		$script = "if (document.querySelector('.exc-message') !== null) { return document.querySelector('#plain-exception').innerText } else { return false; }";
		$return = $this->webdriver->executeScript($script, []);
		if ($return === false) {
			return false;
		} else {
			$error = $return;
			return true;
		}
	}
}
