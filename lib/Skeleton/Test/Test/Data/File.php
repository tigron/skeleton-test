<?php
/**
 * Test_Data_File class
 *
 * @author Lionel Laffineur <lionel@tigron.be>
 */

namespace Skeleton\Test\Test\Data;

class File {
	use \Skeleton\Object\Model;
	use \Skeleton\Object\Get;
	use \Skeleton\Object\Save;
	use \Skeleton\Object\Delete {
		delete as trait_delete;
	}

	/**
	 * Class configuration
	 *
	 * @access private
	 */
	private static $class_configuration = [
		'database_table' => 'test_data_file',
	];

	/**
	 * delete
	 *
	 * @access public
	 */
	public function delete() {
		$file = $this->file;
		$this->file_id = null;
		$this->save();
		$file->delete();
		$this->trait_delete();
	}

	/**
	 * get_by_identifier
	 *
	 * @access public
	 * @param string $identifier
	 * @return self
	 */
	public static function get_by_identifier($identifier): self {
		$db = \Skeleton\Database\Database::Get();
		$id = $db->get_one('SELECT id FROM test_data_file WHERE identifier = ?', [ $identifier ]);
		if ($id === null) {
			throw new \Exception('No test_data_file found with identifier \'' . $identifier . '\'');
		}
		return self::get_by_id($id);
	}

	/**
	 * add_by_path
	 *
	 * @access public
	 * @param string $identifier
	 * @param string $path
	 * @return self
	 */
	public static function add_by_path($identifier, $path): self {
		$content = file_get_contents($path);
		return self::add_by_content($identifier, basename($path), $content);
	}

	/**
	 * add_by_content
	 *
	 * @access public
	 * @param string $identifier
	 * @param string $file_name
	 * @param string $content
	 * @return self
	 */
	public static function add_by_content($identifier, $file_name, $content): self {
		$file = \Skeleton\File\File::store($file_name, $content);
		$test_data_file = new self();
		$test_data_file->identifier = $identifier;
		$test_data_file->file_id = $file->id;
		$test_data_file->save();
		return $test_data_file;
	}
}
