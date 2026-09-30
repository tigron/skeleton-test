<?php
/**
 * test:list command for Skeleton Console
 *
 * Lists the scene classes of the test directory, grouped by the driver
 * they declare. Scenes without a driver declaration are listed as
 * selenium, the framework default.
 *
 * @author Gerry Demaret <gerry@tigron.be>
 */

namespace Skeleton\Console\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

class Test_List extends \Skeleton\Console\Command {

	/**
	 * Configure the command
	 *
	 * @access protected
	 */
	protected function configure() {
		$this->setName('test:list');
		$this->setDescription('List the scene classes declared for a driver');
		$this->addArgument('driver', InputArgument::REQUIRED, 'Driver to list scenes for (playwright, selenium or driverless)');
	}

	/**
	 * Execute the Command
	 *
	 * Scenes declaring a driver are listed for that driver. Scenes without
	 * a driver declaration are driverless: they never touch a browser and
	 * can run on plain php + database infrastructure.
	 *
	 * @access protected
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 */
	protected function execute(InputInterface $input, OutputInterface $output): int {
		$driver = $input->getArgument('driver');
		if (!in_array($driver, [ 'playwright', 'selenium', 'driverless' ])) {
			$output->writeln('<error>Unknown driver: ' . $driver . '</error>');
			return 1;
		}

		$scene_dir = \Skeleton\Test\Config::$test_path . '/Scene';
		if (!file_exists($scene_dir)) {
			$output->writeln('<error>No Scene directory in ' . \Skeleton\Test\Config::$test_path . '</error>');
			return 1;
		}

		$scenes = [];
		$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($scene_dir));
		foreach ($iterator as $file) {
			if ($file->isDir() || $file->getExtension() !== 'php') {
				continue;
			}
			$relative = substr($file->getPathname(), strlen($scene_dir) + 1, -4);
			$scene = 'Scene_' . str_replace('/', '_', $relative);
			$contents = file_get_contents($file->getPathname());
			if (preg_match("/static\s+\\\$driver\s*=\s*'([a-z]+)'/", $contents, $matches)) {
				$scene_driver = $matches[1];
			} else {
				$scene_driver = 'driverless';
			}
			if ($scene_driver === $driver) {
				$scenes[] = $scene;
			}
		}

		sort($scenes);
		$output->writeln(implode(',', $scenes));
		return 0;
	}
}
