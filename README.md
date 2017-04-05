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

	// For timings of the tests (start and stop time of every test)
	\Skeleton\Test\Config::$start_timestamp_filename = sys_get_temp_dir() . '/skeleton-test-start-time.txt';
	\Skeleton\Test\Config::$timings_filename = sys_get_temp_dir() . '/skeleton-test-timings.json';

	// To run the tests
	skeleton test:all

	// or
	skeleton test:run My_First_Test_Class
