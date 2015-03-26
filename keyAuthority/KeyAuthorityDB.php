<?

/*
 * keyAuthority/KeyAuthorityDB.php
 *
 * Helper singleton class for easy access to Key Authority's database
 */

class KeyAuthorityDB extends SQLite3 {

	private static $inst;

	static function instance() {
		if (KeyAuthorityDB::$inst === null) {
			KeyAuthorityDB::$inst = new KeyAuthorityDB();
			KeyAuthorityDB::$inst->open('db');
		}
		return KeyAuthorityDB::$inst;
	}

	public function __construct() {}

}