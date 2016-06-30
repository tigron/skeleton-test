<?php
/**
 * migration:run command for Skeleton Console
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
		$phpunit = new \PHPUnit_TextUI_TestRunner;
		require LIB_PATH . '/external/packages/kujira/phpunit-printer/src/Printer.php';

		$printer = new \PrettyResultPrinter\Printer();

		$test_results = $phpunit->run($phpunit->getTest($directory, '', $input->getArgument('name')), [ 'colors' => 'always', 'verbose' => true, 'debug' => false, 'tap' => true, 'printer' => $printer ]);
		var_dump($test_results);


	}

}
