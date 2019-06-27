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

	// To run a single test
	skeleton test:run My_First_Test_Class

	// To intensively (10x) run a single test
	skeleton test:intense My_First_Test_Class

	// To use files in the tests (only if skeleton-file is available)
	skeleton test:file add identifier /var/www/mysite/my_file.txt
	skeleton test:file get identifier /var/www/mysite/target_file.txt
	skeleton test:file delete identifier
	skeleton test:file list
