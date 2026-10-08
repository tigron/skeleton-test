<?php
/**
 * Printer class
 *
 * A PHPUnit result printer that prints every test on its own line, with a
 * status color and duration.
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 */

namespace Skeleton\Test;

use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestFailure;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Runner\BaseTestRunner;
use PHPUnit\Util\Filter;

class Printer extends \PHPUnit\TextUI\DefaultResultPrinter {
	/**
	 * Class name of the test currently running
	 *
	 * @access protected
	 * @var string $class_name
	 */
	protected $class_name;

	/**
	 * Class name of the previously printed test
	 *
	 * @access protected
	 * @var string $previous_class_name
	 */
	protected $previous_class_name;

	/**
	 * A testsuite started
	 *
	 * @access public
	 * @param TestSuite $suite
	 */
	public function startTestSuite(TestSuite $suite): void {
		parent::startTestSuite($suite);
	}

	/**
	 * A test started
	 *
	 * @access public
	 * @param Test $test
	 */
	public function startTest(Test $test): void {
		$this->class_name = get_class($test);
	}

	/**
	 * A test ended
	 *
	 * @access public
	 * @param Test $test
	 * @param float $time
	 */
	public function endTest(Test $test, float $time): void {
		parent::endTest($test, $time);

		$test_method_name = \PHPUnit\Util\Test::describe($test);

		// Convert capitalized words to lowercase
		$test_method_name[1] = preg_replace_callback('/([A-Z]{2,})/', function ($matches) { return strtolower($matches[0]); }, $test_method_name[1]);

		// Convert non-breaking method name to camelCase
		$test_method_name[1] = str_replace(' ', '', ucwords($test_method_name[1], ' '));

		// Convert snakeCase method name to camelCase
		$test_method_name[1] = str_replace('_', '', ucwords($test_method_name[1], '_'));

		preg_match_all('/((?:^|[A-Z])[a-z0-9]+)/', $test_method_name[1], $matches);

		// Prepend all numbers with a space
		$replaced = preg_replace('/(\d+)/', ' $1', $matches[0]);

		$test_name_array = array_map('strtolower', $replaced);

		$name = implode(' ', $test_name_array);

		// Check if prefix is test, remove it
		$name = preg_replace('/^test /', '', $name, 1);

		// Get the data set name
		$name = $this->handle_data_set_name($name, $test_method_name[1]);

		$color = 'fg-green';
		if ($test->getStatus() !== 0) {
			$color = 'fg-red';
		}

		$this->write(' ');

		switch ($test->getStatus()) {
			case BaseTestRunner::STATUS_PASSED:
				$this->writeWithColor('fg-green', $name, false);

				break;
			case BaseTestRunner::STATUS_SKIPPED:
				$this->writeWithColor('fg-yellow', $name, false);

				break;
			case BaseTestRunner::STATUS_INCOMPLETE:
				$this->writeWithColor('fg-blue', $name, false);

				break;
			case BaseTestRunner::STATUS_FAILURE:
				$this->writeWithColor('fg-red', $name, false);

				break;
			case BaseTestRunner::STATUS_ERROR:
				$this->writeWithColor('fg-red', $name, false);

				break;
			case BaseTestRunner::STATUS_RISKY:
				$this->writeWithColor('fg-magenta', $name, false);

				break;
			case BaseTestRunner::STATUS_WARNING:
				$this->writeWithColor('fg-yellow', $name, false);

				break;
			case BaseTestRunner::STATUS_UNKNOWN:
			default:
				$this->writeWithColor('fg-cyan', $name, false);

				break;
		}

		$this->write(' ');

		$time_color = $time > 0.5 ? 'fg-yellow' : 'fg-white';
		$this->writeWithColor($time_color, '[' . number_format($time, 3) . 's]', true);
	}

	/**
	 * Write the progress of the test run
	 *
	 * @access protected
	 * @param string $progress
	 */
	protected function writeProgress(string $progress): void {
		if ($this->previous_class_name !== $this->class_name) {
			$this->write("\n");
			$this->writeWithColor('bold', $this->class_name, false);
			$this->writeNewLine();
		}

		$this->previous_class_name = $this->class_name;

		$this->print_progress();

		switch (strtoupper(preg_replace('#\\x1b[[][^A-Za-z]*[A-Za-z]#', '', $progress))) {
			case '.':
				$this->writeWithColor('fg-green', '  ✓', false);

				break;
			case 'S':
				$this->writeWithColor('fg-yellow', '  →', false);

				break;
			case 'I':
				$this->writeWithColor('fg-blue', '  ∅', false);

				break;
			case 'F':
				$this->writeWithColor('fg-red', '  x', false);

				break;
			case 'E':
				$this->writeWithColor('fg-red', '  ⚈', false);

				break;
			case 'R':
				$this->writeWithColor('fg-magenta', '  ⌽', false);

				break;
			case 'W':
				$this->writeWithColor('fg-yellow', '  ¤', false);

				break;
			default:
				$this->writeWithColor('fg-cyan', '  ≈', false);

				break;
		}
	}

	/**
	 * Print the trace of a defect
	 *
	 * @access protected
	 * @param TestFailure $defect
	 */
	protected function printDefectTrace(TestFailure $defect): void {
		$this->write($this->format_exception_msg($defect->getExceptionAsString()));
		$trace = Filter::getFilteredStacktrace(
			$defect->thrownException()
		);
		if (!empty($trace)) {
			$this->write("\n" . $trace);
		}
		$exception = $defect->thrownException()->getPrevious();
		while ($exception) {
			$this->write(
				"\nCaused by\n" .
				TestFailure::exceptionToString($exception) . "\n" .
				Filter::getFilteredStacktrace($exception)
			);
			$exception = $exception->getPrevious();
		}
	}

	/**
	 * Colorize an exception message
	 *
	 * @access protected
	 * @param string $exception_message
	 * @return string
	 */
	protected function format_exception_msg($exception_message): string {
		$exception_message = str_replace("+++ Actual\n", '', $exception_message);
		$exception_message = str_replace("--- Expected\n", '', $exception_message);
		$exception_message = str_replace('@@ @@', '', $exception_message);

		if ($this->colors) {
			$exception_message = preg_replace('/^(Exception.*)$/m', "\033[01;31m$1\033[0m", $exception_message);
			$exception_message = preg_replace('/(Failed.*)$/m', "\033[01;31m$1\033[0m", $exception_message);
			$exception_message = preg_replace("/(\-+.*)$/m", "\033[01;32m$1\033[0m", $exception_message);
			$exception_message = preg_replace("/(\++.*)$/m", "\033[01;31m$1\033[0m", $exception_message);
		}

		return $exception_message;
	}

	/**
	 * Append the data set name to a test name
	 *
	 * @access private
	 * @param string $name
	 * @param string $test_method_name
	 * @return string
	 */
	private function handle_data_set_name($name, $test_method_name): string {
		preg_match('/\bwith data set "([^"]+)"/', $test_method_name, $data_set_match);

		if (empty($data_set_match)) {
			return $name;
		}

		return $name . ' [' . $data_set_match[1] . ']';
	}

	/**
	 * Print the current progress counter
	 *
	 * @access private
	 */
	private function print_progress(): void {
		if (filter_var(getenv('PHPUNIT_PRETTY_PRINT_PROGRESS'), FILTER_VALIDATE_BOOLEAN)) {
			$this->numTestsRun++;

			$total = $this->numTests;
			$current = str_pad($this->numTestsRun, strlen($total), '0', STR_PAD_LEFT);

			$this->write("[{$current}/{$total}]");
		}
	}
}
