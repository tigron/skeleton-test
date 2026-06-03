<?php
/**
 * test:playwright:run command for Skeleton Console
 */

namespace Skeleton\Console\Command;

use Symfony\Component\Console\Input\InputArgument;
use Skeleton\Test\Command;

class Test_Playwright_Run extends Command {

	protected function get_description(): string {
		return 'Run a playwright test';
	}

	protected function get_test_type(): string {
		return 'playwright';
	}

	protected function configure_local(): void {
		$this->addArgument('name', InputArgument::REQUIRED, 'Name of the test');
	}

	protected function get_test_suite($test_path): \PHPUnit\Framework\TestSuite {
		$suite = new \PHPUnit\Framework\TestSuite();
		$names = explode(',', $this->input->getArgument('name'));
		foreach ($names as $name) {
			$suite->addTestSuite(trim($name));
		}
		return $suite;
	}

}
