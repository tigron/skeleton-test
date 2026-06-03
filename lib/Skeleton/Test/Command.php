<?php
/**
 * Base class for test console commands
 */

namespace Skeleton\Test;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

abstract class Command extends \Skeleton\Console\Command {

	/**
	 * Input interface
	 *
	 * @access protected
	 */
	protected $input;

	/**
	 * Get the test type
	 *
	 * @access protected
	 */
	abstract protected function get_test_type(): string;

	/**
	 * Get test suite for running tests
	 *
	 * @access protected
	 * @param string $test_path
	 * @return \PHPUnit\Framework\TestSuite
	 */
	abstract protected function get_test_suite($test_path): \PHPUnit\Framework\TestSuite;

	/**
	 * Configure the command
	 *
	 * @access protected
	 */
	protected function configure() {
		$this->setName('test:' . $this->get_test_type() . ':' . $this->get_action());
		$this->setDescription($this->get_description());
		$this->addOption('disable-pretty-printer', null, InputOption::VALUE_NONE, 'Disable PHPUnit\'s PrettyResultPrinter');
		$this->configure_local();
	}

	/**
	 * Get action
	 *
	 * @access protected
	 */
	protected function get_action(): string {
		// all or run
		return strtolower(substr(static::class, -3));
	}

	/**
	 * Add additional options for specific commands
	 *
	 * @access protected
	 */
	protected function configure_local(): void {
	}

	/**
	 * Resolve test path for the given type
	 *
	 * @access protected
	 * @param string $type
	 * @return string
	 * @throws \Exception
	 */
	protected function resolve_test_path($type): string {
		if (!file_exists(Config::$test_path)) {
			throw new \Exception('Config::$test_path is not set to a valid path');
		}

		$test_path = Config::$test_path . '/' . $type;

		if (!file_exists($test_path)) {
			throw new \Exception($type . ' test path does not exist: ' . $test_path);
		}

		return $test_path;
	}

	/**
	 * Register autoloader for test path
	 *
	 * @access protected
	 * @param string $test_path
	 */
	protected function register_autoloader($test_path): void {
		$autoloader = new \Skeleton\Core\Autoloader();
		$autoloader->add_include_path($test_path);
		$autoloader->register();
	}

	/**
	 * Execute the command
	 *
	 * @access protected
	 */
	protected function execute(InputInterface $input, OutputInterface $output): int {
		$this->input = $input;

		try {
			$test_path = $this->resolve_test_path($this->get_test_type());
		} catch (\Exception $e) {
			$output->writeln('<error>' . $e->getMessage() . '</error>');
			return 1;
		}

		$this->register_autoloader($test_path);

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
			$arguments['printer'] = new Printer(null, false, 'always', false, 150);
		}

		$suite = $this->get_test_suite($test_path);
		$phpunit->run($suite, $arguments);
		return 0;
	}

}
