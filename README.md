# skeleton-test
Testing utilities for skeleton


## Installation

Installation via composer:

    composer require tigron/skeleton-test

## Howto

	// Set a directory where tests are located in
	\Skeleton\Test\Config::$test_directory = $root_path . '/tests';

	// For Selenium test, add browser configurations
	\Skeleton\Test\Config::add_browser_configuration('firefox', 'localhost', 4444, 30000)

	// To run the tests
	skeleton test:all

	// or
	skeleton test:run MyFirstTest.php
