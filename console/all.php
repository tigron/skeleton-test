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
		if (\Skeleton\Test\Config::$test_directory !== null) {
			\Skeleton\Test\Config::$test_path = \Skeleton\Test\Config::$test_directory;
		}

		if (!file_exists(\Skeleton\Test\Config::$test_path)) {
			$output->writeln('<error>Config::$test_path is not set to a valid path</error>');
			return 1;
		}

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
			$arguments['printer'] = new \Skeleton\Test\Printer(null, false, 'always', false, 150);
		}


		$declared_classes = get_declared_classes();

		// Load classes inside the given folder:
		$dir_iterator = new \RecursiveDirectoryIterator(\Skeleton\Test\Config::$test_path);
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

		$test_results = $phpunit->run($suite, $arguments);
		return 0;
	}

}
