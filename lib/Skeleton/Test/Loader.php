<?php
/**
 * Loader for test files
 */

namespace Skeleton\Test;

class Loader {

	/**
	 * Require all PHP files in a directory tree
	 *
	 * @access public
	 * @param string $path
	 * @return array classes that were loaded
	 */
	public static function require_all(string $path): array {
		$before = get_declared_classes();

		$dir_iterator = new \RecursiveDirectoryIterator($path);
		$iterator = new \RecursiveIteratorIterator($dir_iterator, \RecursiveIteratorIterator::SELF_FIRST);

		foreach ($iterator as $file) {
			$filename = $file->getFilename();
			if ($filename[0] == '.') {
				continue;
			}
			if (is_dir($file->getPathname())) {
				continue;
			}
			if (substr($filename, -4) !== '.php') {
				continue;
			}
			require_once $file->getPathname();
		}

		return array_diff(get_declared_classes(), $before);
	}

	/**
	 * Filter an array of class names down to the Scene classes
	 *
	 * @access public
	 * @param array $classes
	 * @return array $scenes
	 */
	public static function get_scenes(array $classes): array {
		$scenes = array_filter($classes, function ($class) {
			return strpos($class, 'Scene_') === 0;
		});

		sort($scenes);

		return $scenes;
	}
}
