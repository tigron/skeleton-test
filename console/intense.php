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
	protected function execute(InputInterface $input, OutputInterface $output) {
		if (!file_exists(\Skeleton\Database\Migration\Config::$migration_directory)) {
			$output->writeln('<error>Config::$test_directory is not set to a valid directory</error>');
			return 1;
		}

		$directory = \Skeleton\Test\Config::$test_directory;

		for ($i = 0; $i < \Skeleton\Test\Config::$intense_count; $i++) {
			printf("%d) ", $i);
			$phpunit = new \PHPUnit_TextUI_TestRunner();

			$arguments = [ 'colors' => 'always', 'verbose' => true, 'debug' => true, 'tap' => true, 'loadedExtensions' => [], 'notLoadedExtensions' => []];

			if (!$input->getOption('disable-pretty-printer')) {
				$arguments['printer'] = new \PrettyResultPrinter\Printer();
			}

			$suite = new \PHPUnit_Framework_TestSuite();
			$suite->addTestSuite( $input->getArgument('name') );
			$test_results = $phpunit->doRun($suite, $arguments, false);
		}
	}
}
