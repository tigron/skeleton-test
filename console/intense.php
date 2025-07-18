<?php
/**
 * test:intense command for Skeleton Console
 *
 * @author Lionel Laffineur <lionel@tigron.be>
 */

namespace Skeleton\Console\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Test_Intense extends \Skeleton\Console\Command {

	/**
	 * Configure the Create command
	 *
	 * @access protected
	 */
	protected function configure() {
		$this->setName('test:intense');
		$this->setDescription('Run intensively a test');
		$this->addArgument('name', InputArgument::REQUIRED, 'Name of the test');
		$this->addOption('disable-pretty-printer', null, InputOption::VALUE_NONE, 'Disable PHPUnit\'s PrettyResultPrinter');

	}

	/**
	 * Execute the Command
	 *
	 * @access protected
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 */
	protected function execute(InputInterface $input, OutputInterface $output): int {
		if (\Skeleton\Test\Config::$test_directory !== null) {
			\Skeleton\Test\Config::$test_path = \Skeleton\Test\Config::$test_directory;
		}

		if (!file_exists(\Skeleton\Test\Config::$test_path)) {
			$output->writeln('<error>Config::$test_path is not set to a valid directory</error>');
			return 1;
		}

		$phpunit = new \PHPUnit\TextUI\TestRunner();
		$arguments = [
			'colors' => 'always',
			'verbose' => false,
			'debug' => false,
			'loadedExtensions' => [],
			'notLoadedExtensions' => [],
			'extensions' => [],
			'warnings' => [],
			'stderr' => true,
			'repeat' => \Skeleton\Test\Config::$intense_count,
		];

		if (!$input->getOption('disable-pretty-printer')) {
			$arguments['printer'] = new \Skeleton\Test\Printer(null, false, 'always', false, 150);
		}

		$suite = new \PHPUnit\Framework\TestSuite();
		$names = explode(',', $input->getArgument('name'));
		foreach ($names as $name) {
			$suite->addTestSuite($name);
		}

		$test_results = $phpunit->run($suite, $arguments);
		return 0;
	}
}
