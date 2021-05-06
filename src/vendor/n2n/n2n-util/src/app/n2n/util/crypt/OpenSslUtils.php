<?php
/*
 * Copyright (c) 2012-2016, Hofmänner New Media.
 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
 *
 * This file is part of the N2N FRAMEWORK.
 *
 * The N2N FRAMEWORK is free software: you can redistribute it and/or modify it under the terms of
 * the GNU Lesser General Public License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * N2N is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
 * the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details: http://www.gnu.org/licenses/
 *
 * The following people participated in this project:
 *
 * Andreas von Burg.....: Architect, Lead Developer
 * Bert Hofmänner.......: Idea, Frontend UI, Community Leader, Marketing
 * Thomas Günther.......: Developer, Hangar
 */
namespace n2n\util\crypt;

use lib\n2n\util\crypt\RandomPseudoBytesException;

class OpenSslUtils {
	public static function cipherIvLength($algorithm) {
		$res = @openssl_cipher_iv_length($algorithm);
		if ($res === false && $err = error_get_last()) {
			throw new CipherIvLengthException($err['message']);
		}
		return $res;
	}
	
	public static function randomPseudoBytes($size) {
		$res = @openssl_random_pseudo_bytes($size);
		if ($res === false && $err = error_get_last()) {
			throw new RandomPseudoBytesException($err['message']);
		}
		return $res;
	}
	
	public static function encrypt(string $data, string $method, string $key, int $options = 0, $iv = '') {
		$res = @openssl_encrypt($data, $method, $key, $options, $iv);
		if ($res === false && $err = error_get_last()) {
			throw new EncryptionFailedException($err['message']);
		}
		return $res;
	}
	
	public static function decrypt($data, $method, $key, int $options = 0, $iv = '') {
		$res = @openssl_decrypt($data, $method, $key, $options, $iv);
		if ($res === false && $err = error_get_last()) {
			throw new DecryptionFailedException($err['message']);
		}
		return $res;
	}
}
