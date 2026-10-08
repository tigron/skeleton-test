<?php
/**
 * Selector mapping helper
 *
 * Converts engine-agnostic selector strings into WebDriverBy objects
 * else as css.
 *
 * @author Gerry Demaret <gerry@tigron.be>
 */

namespace Skeleton\Test\Selenium;

use Facebook\WebDriver\WebDriverBy;

class Selector {

	/**
	 * Convert a selector string to a WebDriverBy object
	 *
	 * @access public
	 * @param string $selector
	 * @return WebDriverBy
	 */
	public static function to_by(string $selector): WebDriverBy {
		if (str_starts_with($selector, '/') || str_starts_with($selector, '(')) {
			return WebDriverBy::xpath($selector);
		}

		return WebDriverBy::cssSelector($selector);
	}
}
