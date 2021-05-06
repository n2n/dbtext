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

class Cipher {
	/**
	 * The algorythm and mode for Encryption
	 * @var \n2n\util\crypt\EncryptionDescriptor
	 */
	private $encryptionDescriptor;
	/**
	 * The key to encrypt/decrypt the data (Salt)
	 * @var string
	 */
	private $key;
	/**
	 * The initialisation Vector for block ciphering
	 * @var string
	 */
	private $iv;
	/**
	 * @param EncryptionDescriptor $encryptionDescriptor
	 * @param string $key
	 * @param string $iv
	 */
	public function __construct(EncryptionDescriptor $encryptionDescriptor, $key = null, $iv = null){
		$this->encryptionDescriptor = $encryptionDescriptor;
		
		if (is_null($key)) {
			$key = $encryptionDescriptor->generateKey();
		}
		$this->key = $key;
		
		if (is_null($iv)) {
			$iv = $encryptionDescriptor->generateIv();
		}
		
		$this->iv = $iv;
	}
	
	public function getKey() {
		return $this->key;
	}
	
	public function getIv() {
		return $this->iv;
	}
	
	public function encrypt($data) {
		return OpenSslUtils::encrypt($data, $this->encryptionDescriptor->getAlgorithm(), $this->key, 
				0, $this->iv);
	}
	
	public function decrypt($data) {
		return OpenSslUtils::decrypt($data, $this->encryptionDescriptor->getAlgorithm(), $this->key,
				0, $this->iv);
	}
	
}
