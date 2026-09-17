<?php
/**
 * Skeleton\Test\Page\Element interface
 *
 * Engine-agnostic element interface, implemented by both the selenium and
 * the playwright driver wrappers. Helpers should code against this so they
 * can be used from pages of either engine.
 *
 * @author Gerry Demaret <gerry@tigron.be>
 */

namespace Skeleton\Test\Page;

interface Element {

	/**
	 * Click the element
	 *
	 * @access public
	 */
	public function click(): void;

	/**
	 * Type text into the element
	 *
	 * @access public
	 * @param string $value
	 */
	public function send_keys($value): void;

	/**
	 * Is the element displayed
	 *
	 * @access public
	 * @return bool
	 */
	public function is_displayed(): bool;

	/**
	 * Get the text of the element
	 *
	 * @access public
	 * @return string
	 */
	public function get_text(): string;

	/**
	 * Get an attribute of the element
	 *
	 * @access public
	 * @param string $name
	 * @return string|null
	 */
	public function get_attribute(string $name);

	/**
	 * Find a child element
	 *
	 * @access public
	 * @param string $selector css or xpath selector
	 * @param int $timeout seconds to wait for the element to be attached, 0 = immediate
	 * @return Element
	 * @throws Elementnotfound when the element is not present
	 */
	public function find_element(string $selector, int $timeout = 0): Element;

	/**
	 * Find child elements
	 *
	 * @access public
	 * @param string $selector css or xpath selector
	 * @param int $timeout seconds to wait for the elements to be attached, 0 = immediate
	 * @return array
	 */
	public function find_elements(string $selector, int $timeout = 0): array;
}
