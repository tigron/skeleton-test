<?php
/**
 * test:run command for Skeleton Console
 *
 * @author David Vandemaele <david@tigron.be>
 */

namespace Skeleton\Console\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Test_Run extends \Skeleton\Console\Command {

	/**
	 * Configure the Create command
	 *
	 * @access protected
	 */
	protected function configure() {
		$this->setName('test:run');
		$this->setDescription('Run a test');
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
	protected function execute(InputInterface $input, OutputInterface $output) {
		if (\Skeleton\Test\Config::$test_directory !== null) {
			\Skeleton\Test\Config::$test_path = \Skeleton\Test\Config::$test_directory;
		}

		if (!file_exists(\Skeleton\Test\Config::$test_path)) {
			$output->writeln('<error>Config::$test_path is not set to a valid path</error>');
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
