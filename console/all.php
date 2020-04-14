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

class Test_All extends \Skeleton\Console\Command {

	/**
	 * Configure the Create command
	 *
	 * @access protected
	 */
	protected function configure() {
		$this->setName('test:all');
		$this->setDescription('Run all tests');
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
		if (!file_exists(\Skeleton\Test\Config::$test_directory)) {
			$output->writeln('<error>Config::$test_directory is not set to a valid directory</error>');
			return 1;
		}

		$directory = \Skeleton\Test\Config::$test_directory;
		$phpunit = new \PHPUnit\TextUI\TestRunner;

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
			$arguments['printer'] = new \Sempro\PHPUnitPrettyPrinter\PrettyPrinter(null, false, 'always', false, 150);
		}


		$test_results = $phpunit->run($phpunit->getTest($directory, '', '.php'), $arguments);
		return 0;
	}

}
