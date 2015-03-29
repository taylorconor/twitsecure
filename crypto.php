<?php

class Crypto {

	static function encrypt($value, $key) {
		return rtrim(
			base64_encode(
				mcrypt_encrypt(
					MCRYPT_RIJNDAEL_128,
					$key, $value,
					MCRYPT_MODE_CBC,
					"\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0"
				)
			), "\0"
		);
	}

	static function decrypt($value, $key) {
		return rtrim(
			mcrypt_decrypt(
				MCRYPT_RIJNDAEL_128,
				$key, base64_decode($value),
				MCRYPT_MODE_CBC,
				"\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0"
			), "\0"
		);
	}
}