<?php
/**
 * Transitional Selenium Page class
 *
 * Pages that are not yet ported to the engine-agnostic Driver API extend
 * this class to keep direct webdriver access. They can only run on
 * selenium: the class forces the selenium driver and the scene it is used
 * from must run on the same driver. Delete this class when all pages are
 * ported.
 *
 * @author Gerry Demaret <gerry@tigron.be>
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

namespace Skeleton\Test\Page;

use Skeleton\Test\Selenium\Webdriver;

abstract class Selenium extends \Skeleton\Test\Page {

	/**
	 * Webdriver session
	 *
	 * @access protected
	 * @var Webdriver $webdriver
	 */
	protected Webdriver $webdriver;

	/**
	 * Construct
	 *
	 * @access public
	 */
	public function __construct() {
		parent::__construct('selenium');
		$this->webdriver = Webdriver::initiate();
		$this->webdriver->manage()->window()->maximize();
	}

	/**
	 * Get webdriver
	 *
	 * @access protected
	 * @return Webdriver $webdriver
	 */
	protected function get_webdriver(): Webdriver {
		return $this->webdriver;
	}
}
