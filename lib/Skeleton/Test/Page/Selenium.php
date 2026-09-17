<?php
/**
 * Page class
 *
 * @author Gerry Demaret <gerry@tigron.be>
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

namespace Skeleton\Test\Page;

abstract class Selenium extends \Skeleton\Test\Page {

	/**
	 * Webdriver variable
	 *
	 * @access protected
	 * @var \Skeleton\Test\Selenium\Webdriver $webdriver
	 */
	protected $webdriver = null;

	/**
	 * Construct
	 *
	 * @access public
	 */
	public function __construct() {
		$this->get_webdriver()->manage()->window()->maximize();
	}

	/**
	 * Get webdriver
	 *
	 * @access protected
	 * @return Skeleton\Test\Selenium\Webdriver $webdriver
	 */
	protected function get_webdriver(): \Skeleton\Test\Selenium\Webdriver {
		if (empty($this->webdriver)) {
			$this->webdriver = \Skeleton\Test\Selenium\Webdriver::initiate();
			$this->webdriver->page = $this;
		}
		return $this->webdriver;
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
		$this->get_webdriver()->get($this->get_url());
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
		$return = $this->get_webdriver()->executeScript($script, []);

		if ($return === false) {
			return false;
		} else {
			$error = $return;
			return true;
		}
	}
}
