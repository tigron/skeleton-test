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
	 * @var \Skeleton\Selenium\Webdriver $webdriver
	 */
	private $webdriver = null;

	/**
	 * Construct
	 *
	 * @access public
	 * @param \Facebook\Webdriver\WebDriver
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
}
