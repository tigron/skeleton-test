<?php
/**
 * Elementnotfound exception
 *
 * Thrown by the engine-agnostic find_element()/wait_*() API when an element
 * is not present, regardless of the engine (selenium or playwright) in use.
 *
 * @author Gerry Demaret <gerry@tigron.be>
 */

namespace Skeleton\Test\Page;

class Elementnotfound extends \Exception {
}
