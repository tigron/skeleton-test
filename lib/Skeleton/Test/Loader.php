<?php
/**
 * Loader for test files
 *
 * @author Gerry Demaret <gerry@tigron.be>
 */

namespace Skeleton\Test;

class Loader {

	/**
	 * Register an autoloader for the test files
	 *
	 * Test classes follow the underscore-to-path convention (e.g.
	 * Scene_Myknx_Shop_Search lives in <path>/Scene/Myknx/Shop/Search.php),
	 * so they can be autoloaded in the correct order (traits and parent
	 * classes are loaded on first use).
	 *
	 * @access public
	 * @param string $path
	 */
	public static function register_autoloader(string $path): void {
		$autoloader = new \Skeleton\Core\Autoloader();
		$autoloader->add_include_path($path);
		$autoloader->register();
	}

	/**
	 * Get all Scene class names in a directory tree
	 *
	 * The files are token-scanned for "class Scene_*" declarations, nothing
	 * is loaded. The classes are autoloaded when the test suite runs.
	 *
	 * @access public
	 * @param string $path
	 * @return array scene class names
	 */
	public static function get_scenes(string $path): array {
		$scenes = [];

		$dir_iterator = new \RecursiveDirectoryIterator($path);
		$iterator = new \RecursiveIteratorIterator($dir_iterator, \RecursiveIteratorIterator::LEAVES_ONLY);

		foreach ($iterator as $file) {
			$filename = $file->getFilename();
			if ($filename[0] === '.') {
				continue;
			}
			if (substr($filename, -4) !== '.php') {
				continue;
			}

			$tokens = token_get_all(file_get_contents($file->getPathname()));
			$token_count = count($tokens);

			for ($i = 0; $i < $token_count; $i++) {
				if (!is_array($tokens[$i]) || $tokens[$i][0] !== T_CLASS) {
					continue;
				}

				$j = $i + 1;
				while ($j < $token_count && is_array($tokens[$j]) && in_array($tokens[$j][0], [ T_WHITESPACE, T_COMMENT, T_DOC_COMMENT ])) {
					$j++;
				}

				if ($j < $token_count && is_array($tokens[$j]) && $tokens[$j][0] === T_STRING) {
					$class_name = $tokens[$j][1];
					if (str_starts_with($class_name, 'Scene_')) {
						$scenes[] = $class_name;
					}
				}
			}
		}

		sort($scenes);

		return array_unique($scenes);
	}
}
