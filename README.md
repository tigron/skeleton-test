# skeleton-test
Testing utilities for skeleton


## Installation

Installation via composer:

    composer require tigron/skeleton-test

## Howto

	// Set a directory where tests are located in
	\Skeleton\Test\Config::$test_directory = /my_tests';

	// For Selenium test, set the Selenium hub URL
	\Skeleton\Test\Config::$selenium_hub = 'http://localhost:4444/wd/hub';

	// To run the tests
	skeleton test:all

	// or
	skeleton test:run My_First_Test_Class
