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

class EncryptionDescriptor {
	
	const ALGORITHM_AES_128_CBC = 'aes-128-cbc';
	const ALGORITHM_AES_128_CCM = 'aes-128-ccm';
	const ALGORITHM_AES_128_CFB = 'aes-128-cfb';
	const ALGORITHM_AES_128_CFB1 = 'aes-128-cfb1';
	const ALGORITHM_AES_128_CFB8 = 'aes-128-cfb8';
	const ALGORITHM_AES_128_CTR = 'aes-128-ctr';
	const ALGORITHM_AES_128_ECB = 'aes-128-ecb';
	const ALGORITHM_AES_128_GCM = 'aes-128-gcm';
	const ALGORITHM_AES_128_OFB = 'aes-128-ofb';
	const ALGORITHM_AES_128_XTS = 'aes-128-xts';
	
	const ALGORITHM_AES_192_CBC = 'aes-192-cbc';
	const ALGORITHM_AES_192_CCM = 'aes-192-ccm';
	const ALGORITHM_AES_192_CFB = 'aes-192-cfb';
	const ALGORITHM_AES_192_CFB1 = 'aes-192-cfb1';
	const ALGORITHM_AES_192_CFB8 = 'aes-192-cfb8';
	const ALGORITHM_AES_192_CTR = 'aes-192-ctr';
	const ALGORITHM_AES_192_ECB = 'aes-192-ecb';
	const ALGORITHM_AES_192_GCM = 'aes-192-gcm';
	const ALGORITHM_AES_192_OFB = 'aes-192-ofb';
	
	const ALGORITHM_AES_256_CBC = 'aes-256-cbc';
	const ALGORITHM_AES_256_CFB = 'aes-256-cfb';
	const ALGORITHM_AES_256_CCM = 'aes-256-ccm';
	const ALGORITHM_AES_256_CFB1 = 'aes-256-cfb1';
	const ALGORITHM_AES_256_CFB8 = 'aes-256-cfb8';
	const ALGORITHM_AES_256_CTR = 'aes-256-ctr';
	const ALGORITHM_AES_256_ECB = 'aes-256-ecb';
	const ALGORITHM_AES_256_GCM = 'aes-256-gcm';
	const ALGORITHM_AES_256_OFB = 'aes-256-ofb';
	const ALGORITHM_AES_256_XTS = 'aes-256-xts';
	
	const ALGORITHM_BF_CBC = 'bf-cbc';
	const ALGORITHM_BF_CFB = 'bf-cfb';
	const ALGORITHM_BF_ECB = 'bf-ecb';
	const ALGORITHM_BF_OFB = 'bf-ofb';

	const ALGORITHM_CAMELLIA_128_CBC = 'camellia-128-cbc';
	const ALGORITHM_CAMELLIA_128_CFB = 'camellia-128-cfb';
	const ALGORITHM_CAMELLIA_128_CFB1 = 'camellia-128-cfb1';
	const ALGORITHM_CAMELLIA_128_CFB8 = 'camellia-128-cfb8';
	const ALGORITHM_CAMELLIA_128_ECB = 'camellia-128-ecb';
	const ALGORITHM_CAMELLIA_128_OFB = 'camellia-128-ofb';

	const ALGORITHM_CAMELLIA_192_CBC = 'camellia-192-cbc';
	const ALGORITHM_CAMELLIA_192_CFB = 'camellia-192-cfb';
	const ALGORITHM_CAMELLIA_192_CFB1 = 'camellia-192-cfb1';
	const ALGORITHM_CAMELLIA_192_CFB8 = 'camellia-192-cfb8';
	const ALGORITHM_CAMELLIA_192_ECB = 'camellia-192-ecb';
	const ALGORITHM_CAMELLIA_192_OFB = 'camellia-192-ofb';

	const ALGORITHM_CAMELLIA_256_CBC = 'camellia-256-cbc';
	const ALGORITHM_CAMELLIA_256_CFB = 'camellia-256-cfb';
	const ALGORITHM_CAMELLIA_256_CFB1 = 'camellia-256-cfb1';
	const ALGORITHM_CAMELLIA_256_CFB8 = 'camellia-256-cfb8';
	const ALGORITHM_CAMELLIA_256_ECB = 'camellia-256-ecb';
	const ALGORITHM_CAMELLIA_256_OFB = 'camellia-256-ofb';
	
	const ALGORITHM_CAST5_CBC = 'cast5-cbc';
	const ALGORITHM_CAST5_CFB = 'cast5-cfb';
	const ALGORITHM_CAST5_ECB = 'cast5-ecb';
	const ALGORITHM_CAST5_OFB = 'cast5-ofb';

	const ALGORITHM_DES_CBC = 'des-cbc';
	const ALGORITHM_DES_CFB = 'des-cfb';
	const ALGORITHM_DES_CFB1 = 'des-cfb1';
	const ALGORITHM_DES_CFB8 = 'des-cfb8';
	const ALGORITHM_DES_ECB = 'des-ecb';
	const ALGORITHM_DES_OFB = 'des-ofb';
	
	const ALGORITHM_DES_EDE = 'des-ede';
	const ALGORITHM_DES_EDE_CBC = 'des-ede-cbc';
	const ALGORITHM_DES_EDE_CFB = 'des-ede-cfb';
	const ALGORITHM_DES_EDE_OFB = 'des-ede-ofb';

	const ALGORITHM_DES_EDE3 = 'des-ede3';
	const ALGORITHM_DES_EDE3_CBC = 'des-ede3-cbc';
	const ALGORITHM_DES_EDE3_CFB = 'des-ede3-cfb';
	const ALGORITHM_DES_EDE3_CFB1 = 'des-ede3-cfb1';
	const ALGORITHM_DES_EDE3_CFB8 = 'des-ede3-cfb8';
	const ALGORITHM_DES_EDE3_OFB = 'des-ede3-ofb';
	
	const ALGORITHM_DESX_CBC = 'DESX-cbc';
	
	const ALGORITHM_ID_AES128_CCM = 'id-aes128-ccm';
	const ALGORITHM_ID_AES128_GCM = 'id-aes128-gcm';
	const ALGORITHM_ID_AES128_WRAP = 'id-aes128-wrap';
	
	const ALGORITHM_ID_AES192_CCM = 'id-aes192-ccm';
	const ALGORITHM_ID_AES192_GCM = 'id-aes192-gcm';
	const ALGORITHM_ID_AES192_WRAP = 'id-aes192-wrap';
	
	const ALGORITHM_ID_AES256_CCM = 'id-aes256-ccm';
	const ALGORITHM_ID_AES256_GCM = 'id-aes256-gcm';
	const ALGORITHM_ID_AES256_WRAP = 'id-aes256-wrap';
	
	const ALGORITHM_ID_SMIME_ALG_CMS3DESWRAP = 'id-smime-alg-cms3deswrap';
	
	const ALGORITHM_IDEA_CBC = 'idea-cbc';
	const ALGORITHM_IDEA_CFB = 'idea-cfb';
	const ALGORITHM_IDEA_ECB = 'idea-ecb';
	const ALGORITHM_IDEA_OFB = 'idea-ofb';
	
	const ALGORITHM_RC2_40_CBC = 'rc2-40-cbc';
	const ALGORITHM_RC2_64_CBC = 'rc2-64-cbc';
	const ALGORITHM_RC2_CBC = 'rc2-cbc';
	const ALGORITHM_RC2_CFB = 'rc2-cfb';
	const ALGORITHM_RC2_ECB = 'rc2-ecb';
	const ALGORITHM_RC2_OFB = 'rc2-ofb';

	const ALGORITHM_RC4 = 'rc4';
	const ALGORITHM_RC4_40 = 'rc4-40';
	const ALGORITHM_RC4_HMAC_MD5= 'rc4-hmac-md5';
	
	const ALGORITHM_SEED_CBC = 'seed-cbc';
	const ALGORITHM_SEED_CFB = 'seed-cfb';
	const ALGORITHM_SEED_ECB = 'seed-ecb';
	const ALGORITHM_SEED_OFB = 'seed-ofb';

	const DEFAULT_CRYPT_ALGORITHM = self::ALGORITHM_AES_256_CTR;
	/**
	* the openssl algorithm
	* initialised with the AES algorithm 
	* if you need a faster algorithm it is supposed to use ALGORITHM_AES_128_CBC
	* @var string
	*/
	private $algorithm;
	
	public function __construct($algorithm = self::DEFAULT_CRYPT_ALGORITHM) {
		$this->setAlgorithm($algorithm);
	}
	
	public function getAlgorithm() {
		return $this->algorithm;
	}
	
	public function setAlgorithm($algorithm) {
		$algorithm = strtolower($algorithm);
		if (!self::isAlgorithmAvailable($algorithm)) {
			throw new \InvalidArgumentException('n2n_error_crypt_algorithm_is_not_available: ' . $algorithm);
		}
		$this->algorithm = $algorithm;
	}
	
	public function generateKey() {
		if(!($length = $this->getKeySize())) return null;
		
		return OpenSslUtils::randomPseudoBytes($length);
	}
	
	public function generateIv() {
		if(!($length = $this->getIvSize())) return null;
		
		return OpenSslUtils::randomPseudoBytes($length);
	}
	
	public function getIvSize() {
		return OpenSslUtils::cipherIvLength($this->algorithm);
	}
	
	/**
	 * Deterined the key size using @see https://wiki.openssl.org/index.php/Manual:Enc(1)
	 */
	public function getKeySize() {
		switch ($this->algorithm) {
			case self::ALGORITHM_AES_128_CBC:
			case self::ALGORITHM_AES_128_CCM:
			case self::ALGORITHM_AES_128_CFB:
			case self::ALGORITHM_AES_128_CFB1:
			case self::ALGORITHM_AES_128_CFB8:
			case self::ALGORITHM_AES_128_CTR:
			case self::ALGORITHM_AES_128_ECB:
			case self::ALGORITHM_AES_128_GCM:
			case self::ALGORITHM_AES_128_OFB:
			case self::ALGORITHM_AES_128_XTS:
			case self::ALGORITHM_CAMELLIA_128_CBC:
			case self::ALGORITHM_CAMELLIA_128_CFB:
			case self::ALGORITHM_CAMELLIA_128_CFB1:
			case self::ALGORITHM_CAMELLIA_128_CFB8:
			case self::ALGORITHM_CAMELLIA_128_ECB:
			case self::ALGORITHM_CAMELLIA_128_OFB:
			case self::ALGORITHM_ID_AES128_CCM:
			case self::ALGORITHM_ID_AES128_GCM:
			case self::ALGORITHM_ID_AES128_WRAP:
			//@see: https://wiki.openssl.org/index.php/Manual:Enc(1)
			//->Blowfish and RC5 algorithms use a 128 bit key. 
			case self::ALGORITHM_BF_CBC:
			case self::ALGORITHM_BF_CFB:
			case self::ALGORITHM_BF_ECB:
			case self::ALGORITHM_BF_OFB:
			//@see http://www.gnu.org/software/gnu-crypto/manual/api/gnu/crypto/cipher/Cast5.html
			//-> since the CAST5 key schedule assumes an input key of 128 bits
			case self::ALGORITHM_CAST5_CBC:
			case self::ALGORITHM_CAST5_CFB:
			case self::ALGORITHM_CAST5_ECB:
			case self::ALGORITHM_CAST5_OFB:
			//@see https://en.wikipedia.org/wiki/International_Data_Encryption_Algorithm
			case self::ALGORITHM_IDEA_CBC:
			case self::ALGORITHM_IDEA_CFB:
			case self::ALGORITHM_IDEA_ECB:
			case self::ALGORITHM_IDEA_OFB:
			//@see https://wiki.openssl.org/index.php/Manual:Enc(1)
			case self::ALGORITHM_RC2_CBC:
			case self::ALGORITHM_RC2_CFB:
			case self::ALGORITHM_RC2_ECB:
			case self::ALGORITHM_RC2_OFB:
			case self::ALGORITHM_RC4:
			case self::ALGORITHM_SEED_CBC:
			case self::ALGORITHM_SEED_CFB:
			case self::ALGORITHM_SEED_ECB:
			case self::ALGORITHM_SEED_OFB:
				return 16;
				
			//@asee https://www.tutorialspoint.com/cryptography/triple_des.htm
			case self::ALGORITHM_DES_EDE:
			case self::ALGORITHM_DES_EDE_CBC:
			case self::ALGORITHM_DES_EDE_CFB:
			case self::ALGORITHM_DES_EDE_OFB:
				return 14;
			
			case self::ALGORITHM_AES_192_CBC:
			case self::ALGORITHM_AES_192_CCM:
			case self::ALGORITHM_AES_192_CFB:
			case self::ALGORITHM_AES_192_CFB1:
			case self::ALGORITHM_AES_192_CFB8:
			case self::ALGORITHM_AES_192_CTR:
			case self::ALGORITHM_AES_192_ECB:
			case self::ALGORITHM_AES_192_GCM:
			case self::ALGORITHM_AES_192_OFB:
			case self::ALGORITHM_CAMELLIA_192_CBC:
			case self::ALGORITHM_CAMELLIA_192_CFB:
			case self::ALGORITHM_CAMELLIA_192_CFB1:
			case self::ALGORITHM_CAMELLIA_192_CFB8:
			case self::ALGORITHM_CAMELLIA_192_ECB:
			case self::ALGORITHM_CAMELLIA_192_OFB:
			case self::ALGORITHM_ID_AES192_CCM:
			case self::ALGORITHM_ID_AES192_GCM:
			case self::ALGORITHM_ID_AES192_WRAP:
				return 24;
			
			//@asee https://www.tutorialspoint.com/cryptography/triple_des.htm
			case self::ALGORITHM_DES_EDE3:
			case self::ALGORITHM_DES_EDE3_CBC:
			case self::ALGORITHM_DES_EDE3_CFB:
			case self::ALGORITHM_DES_EDE3_CFB1:
			case self::ALGORITHM_DES_EDE3_CFB8:
			case self::ALGORITHM_DES_EDE3_OFB:
				return 21;
				
			
			case self::ALGORITHM_AES_256_CBC:
			case self::ALGORITHM_AES_256_CFB:
			case self::ALGORITHM_AES_256_CCM:
			case self::ALGORITHM_AES_256_CFB1:
			case self::ALGORITHM_AES_256_CFB8:
			case self::ALGORITHM_AES_256_CTR:
			case self::ALGORITHM_AES_256_ECB:
			case self::ALGORITHM_AES_256_GCM:
			case self::ALGORITHM_AES_256_OFB:
			case self::ALGORITHM_AES_256_XTS:
			case self::ALGORITHM_CAMELLIA_256_CBC:
			case self::ALGORITHM_CAMELLIA_256_CFB:
			case self::ALGORITHM_CAMELLIA_256_CFB1:
			case self::ALGORITHM_CAMELLIA_256_CFB8:
			case self::ALGORITHM_CAMELLIA_256_ECB:
			case self::ALGORITHM_CAMELLIA_256_OFB:
			case self::ALGORITHM_ID_AES256_CCM:
			case self::ALGORITHM_ID_AES256_GCM:
			case self::ALGORITHM_ID_AES256_WRAP:
				return 32;
			
			
			//@see https://en.wikipedia.org/wiki/Data_Encryption_Standard
			case self::ALGORITHM_DES_CBC:
			case self::ALGORITHM_DES_CFB:
			case self::ALGORITHM_DES_CFB1:
			case self::ALGORITHM_DES_CFB8:
			case self::ALGORITHM_DES_ECB:
			case self::ALGORITHM_DES_OFB:
			//@see https://en.wikipedia.org/wiki/des-X
			case self::ALGORITHM_DESX_CBC:
				return 7;
			

			case self::ALGORITHM_RC2_40_CBC:
 			case self::ALGORITHM_RC4_40:
				return 5;
				
 			case self::ALGORITHM_RC2_64_CBC:
 				return 8;
 			
			return 128;
		}
	}
	
	public static function isAlgorithmAvailable($algorithm) {
		return in_array($algorithm, self::getAvailableAlgorithms());
	}
	
	public static function getAvailableAlgorithms() {
		return openssl_get_cipher_methods();
	}
}