<?php
/**
 * test:file command for Skeleton Console
 *
 * @author Lionel Laffineur <lionel@tigron.be>
 */

namespace Skeleton\Console\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;

class Test_File extends \Skeleton\Console\Command {

	/**
	 * Configure the Create command
	 *
	 * @access protected
	 */
	protected function configure() {
		$this->setName('test:file');
		if (class_exists("\Skeleton\File\File")) {
			$this->setDescription('Insert/remove/list test data files');
			$this->addArgument('action', InputArgument::REQUIRED, 'Action');
			$this->addArgument('identifier', InputArgument::OPTIONAL, 'Identifier of the data test file');
			$this->addArgument('path', InputArgument::OPTIONAL, 'Path to the file');
		} else {
			$this->setDescription('Not available, install skeleton-file first (`composer require tigron/skeleton-file`)');
		}
	}

	/**
	 * Execute the Command
	 *
	 * @access protected
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 */
	protected function execute(InputInterface $input, OutputInterface $output) {
		$action = $input->getArgument('action');
		if ($action == 'add') {
			$identifier = $input->getArgument('identifier');
			if ($identifier == null || $identifier == '') {
				$output->writeln('<error>Identifier parameter not provided</error>');
				return 1;
			}
			$path = $input->getArgument('path');
			if ($path == null || $path == '') {
				$output->writeln('<error>Path parameter not provided</error>');
				return 1;
			}
			$test_data_file = \Skeleton\Test\Test\Data\File::add_by_path($identifier, $path);
		} else if ($action == 'delete') {
			$identifier = $input->getArgument('identifier');
			if ($identifier == null || $identifier == '') {
				$output->writeln('<error>Identifier parameter not provided</error>');
				return 1;
			}
			$test_data_file = \Skeleton\Test\Test\Data\File::get_by_identifier($identifier);
			$test_data_file->delete();
		} else if ($action == 'get') {
			$identifier = $input->getArgument('identifier');
			if ($identifier == null || $identifier == '') {
				$output->writeln('<error>Identifier parameter not provided</error>');
				return 1;
			}
			$path = $input->getArgument('path');
			if ($path == null || $path == '') {
				$output->writeln('<error>Path parameter not provided</error>');
				return 1;
			}
			$test_data_file = \Skeleton\Test\Test\Data\File::get_by_identifier($identifier);
			file_put_contents($path, $test_data_file->file->get_contents());
		} else if ($action == 'list') {
			$test_data_files = \Skeleton\Test\Test\Data\File::get_all('identifier');

			$table = new Table($output);
			$table->setHeaders(['ID', 'Identifier', 'Name', 'Size']);
			$rows = [];

			foreach ($test_data_files as $test_data_file) {
				$file = $test_data_file->file;
				$rows[] = [ $test_data_file->id, $test_data_file->identifier, $file->name, $file->get_human_size() ];
			}

			$table->setRows($rows);
			$table->render();
		} else {
			$output->writeln('<error>Invalid action [add|list|delete]</error>');
		}
		return 0;
	}
}
