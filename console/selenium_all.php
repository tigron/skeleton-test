<?php
/**
 * test:selenium:all command for Skeleton Console
 */

namespace Skeleton\Console\Command;

use Skeleton\Test\Command;

class Test_Selenium_All extends Command {

	protected function get_description(): string {
		return 'Run all selenium tests';
	}


	protected function get_test_type(): string {
		return 'selenium';
	}

	protected function get_test_suite($test_path): \PHPUnit\Framework\TestSuite {
		$declared_classes = get_declared_classes();

		$dir_iterator = new \RecursiveDirectoryIterator($test_path);
		$iterator = new \RecursiveIteratorIterator($dir_iterator, \RecursiveIteratorIterator::SELF_FIRST);

		foreach ($iterator as $file) {
			$filename = $file->getFilename();
			if ($filename[0] == '.') {
				continue;
			}
			if (is_dir($file->getPathname())) {
				continue;
			}
			require_once $file->getPathname();
		}

		$scenes = array_diff(get_declared_classes(), $declared_classes);

		foreach ($scenes as $key => $scene) {
			if (strpos($scene, 'Scene_') !== 0) {
				unset($scenes[$key]);
			}
		}
		sort($scenes);

		$suite = new \PHPUnit\Framework\TestSuite();
		foreach ($scenes as $scene) {
			$suite->addTestSuite($scene);
		}

		return $suite;
	}

}
