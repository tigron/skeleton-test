<?php
/**
 * Database migration class
 *
 * @author Lionel Laffineur <lionel@tigron.be>
 */

namespace Skeleton\Test;

use \Skeleton\Database\Database;

class Migration_20190627_135359_Skeleton_test_data_file extends \Skeleton\Database\Migration {

	/**
	 * Migrate up
	 *
	 * @access public
	 */
	public function up() {
		$db = Database::get();
		$db->query("CREATE TABLE `test_data_file` (
					  `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
					  `file_id` int(11) unsigned NULL,
					  `identifier` varchar(64) NOT NULL,
					  `created` datetime NOT NULL,
					  `updated` datetime NULL,
					  FOREIGN KEY (`file_id`) REFERENCES `file` (`id`)
					) ENGINE='InnoDB';");
		$db->query("ALTER TABLE `test_data_file`
					ADD UNIQUE `identifier` (`identifier`);");
	}

	/**
	 * Migrate down
	 *
	 * @access public
	 */
	public function down() {

	}
}
