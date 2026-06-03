<?php
/**
 * Playwright Page class
 *
 * @author Gerry Demaret <gerry@tigron.be>
 */

namespace Skeleton\Test\Page;

use Playwright\Browser\Browser;
use Playwright\Page\Page as PlaywrightPage;

abstract class Playwright extends \Skeleton\Test\Page {

	/**
	 * playright browser
	 *
	 * @access protected
	 * @var Browser $browser
	 */
	protected $browser = null;

	/**
	 * page
	 *
	 * @access protected
	 * @var PlaywrightPage $page
	 */
	protected $page = null;

	/**
	 * @param Browser $browser
	 * @param PlaywrightPage $page
	 */
	public function __construct(Browser $browser, PlaywrightPage $page) {
		$this->browser = $browser;
		$this->page = $page;
	}

	/**
	 * Open page
	 *
	 * @access public
	 */
	public function open() {
		$this->page->goto($this->get_url());
		$this->check_error();
	}

	/**
	 * make errors an exception
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
		try {
			$error_text = $this->page->evaluate("
				() => {
					if (document.querySelector('.exc-message') !== null) {
						return document.querySelector('#plain-exception').innerText;
					}
					return false;
				}
			");

			if ($error_text === false) {
				return false;
			}

			$error = $error_text;


			return true;
		} catch (\Exception $e) {
			return false;
		}
	}
}
