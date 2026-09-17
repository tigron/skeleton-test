<?php
/**
 * Selenium implementation of the engine-agnostic Element interface
 *
 * Wraps Skeleton\Test\Selenium\Webdriver\Element
 *
 * @author Gerry Demaret <gerry@tigron.be>
 */

namespace Skeleton\Test\Page\Selenium;

class Element implements \Skeleton\Test\Page\Element {

	/**
	 * Constructor
	 *
	 * @access public
	 * @param \Skeleton\Test\Selenium\Webdriver\Element $element
	 */
	public function __construct(private \Skeleton\Test\Selenium\Webdriver\Element $element) {
	}

	/**
	 * Click the element
	 *
	 * @access public
	 */
	public function click(): void {
		$this->element->click([ 'stale' => true ]);
	}

	/**
	 * Type text into the element
	 *
	 * @access public
	 * @param string $value
	 */
	public function send_keys($value): void {
		$this->element->sendKeys($value);
	}

	/**
	 * Is the element displayed
	 *
	 * @access public
	 * @return bool
	 */
	public function is_displayed(): bool {
		return $this->element->isDisplayed();
	}

	/**
	 * Get the text of the element
	 *
	 * @access public
	 * @return string
	 */
	public function get_text(): string {
		return $this->element->getText();
	}

	/**
	 * Get an attribute of the element
	 *
	 * @access public
	 * @param string $name
	 * @return string|null
	 */
	public function get_attribute(string $name) {
		return $this->element->getAttribute($name);
	}

	/**
	 * Find a child element
	 *
	 * @access public
	 * @param string $selector css or xpath selector
	 * @param int $timeout seconds to wait for the element to be attached, 0 = immediate
	 * @return Element
	 * @throws \Skeleton\Test\Page\Elementnotfound when the element is not present
	 */
	public function find_element(string $selector, int $timeout = 0): \Skeleton\Test\Page\Element {
		try {
			$element = $this->element->findElement(\Skeleton\Test\Selenium\Selector::to_by($selector), $timeout * 1000);
		} catch (\Facebook\WebDriver\Exception\NoSuchElementException $e) {
			throw new \Skeleton\Test\Page\Elementnotfound('Element not found: ' . $selector);
		}

		return new self($element);
	}

	/**
	 * Find child elements
	 *
	 * @access public
	 * @param string $selector css or xpath selector
	 * @param int $timeout seconds to wait for the elements to be attached, 0 = immediate
	 * @return array
	 */
	public function find_elements(string $selector, int $timeout = 0): array {
		try {
			$elements = $this->element->findElements(\Skeleton\Test\Selenium\Selector::to_by($selector), $timeout * 1000);
		} catch (\Facebook\WebDriver\Exception\NoSuchElementException $e) {
			return [];
		}

		$result = [];
		foreach ($elements as $element) {
			$result[] = new self($element);
		}

		return $result;
	}
}
