<?php
/**
 * Playwright implementation of the engine-agnostic Element interface
 *
 * Wraps Playwright\Locator\Locator
 *
 * @author Gerry Demaret <gerry@tigron.be>
 */

namespace Skeleton\Test\Page\Playwright;

use Playwright\Exception\TimeoutException;
use Playwright\Locator\Locator;

class Element implements \Skeleton\Test\Page\Element {

	/**
	 * Constructor
	 *
	 * @access public
	 * @param Locator $locator
	 */
	public function __construct(private Locator $locator) {
	}

	/**
	 * Click the element
	 *
	 * Uses the first match, mirroring the selenium findElement semantics.
	 *
	 * @access public
	 */
	public function click(): void {
		$this->locator->first()->click();
	}

	/**
	 * Type text into the element
	 *
	 * @access public
	 * @param string $value
	 */
	public function send_keys($value): void {
		$this->locator->first()->fill($value);
	}

	/**
	 * Is the element displayed
	 *
	 * @access public
	 * @return bool
	 */
	public function is_displayed(): bool {
		return $this->locator->first()->isVisible();
	}

	/**
	 * Get the text of the element
	 *
	 * @access public
	 * @return string
	 */
	public function get_text(): string {
		return $this->locator->first()->innerText();
	}

	/**
	 * Get an attribute of the element
	 *
	 * @access public
	 * @param string $name
	 * @return string|null
	 */
	public function get_attribute(string $name) {
		return $this->locator->first()->getAttribute($name);
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
		$element = $this->locator->first()->locator($selector);

		if ($timeout === 0) {
			if ($element->count() === 0) {
				throw new \Skeleton\Test\Page\Elementnotfound('Element not found: ' . $selector);
			}
		} else {
			try {
				$element->waitForAttached([ 'timeout' => $timeout * 1000 ]);
			} catch (TimeoutException $e) {
				throw new \Skeleton\Test\Page\Elementnotfound('Element not found: ' . $selector);
			}
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
		$elements = [];

		foreach ($this->locator->first()->locator($selector)->all() as $locator) {
			$elements[] = new self($locator);
		}

		return $elements;
	}
}
