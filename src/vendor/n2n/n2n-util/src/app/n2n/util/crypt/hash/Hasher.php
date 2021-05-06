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
namespace n2n\util\crypt\hash;

use n2n\util\crypt\hash\algorithm\HashAlgorithm;

class Hasher {
	const PLACEHOLDER_SALT_LENGTH = ':salt_length';
	const REG_EXP_SALT = '/^[A-Za-z0-9\.\/]{:salt_length}$/';
	/**
	 * @var \n2n\util\crypt\hash\algorithm\HashAlgorithm
	 */
	private $algorithm;
	/**
	 * @var string
	 */
	private $salt;
	
	public function __construct(HashAlgorithm $algorithm, $salt = null) {
		$this->algorithm = $algorithm;
		if (is_null($salt)) {
			$salt = $this->generateSalt();
		}
		$this->salt = $salt;
	}
	
	/**
	 * @return string
	 */
	public function generateSalt() {
		$salt = '';
		while (!preg_match($this->getSaltRegExp(), $salt)) {
			$salt = substr(base64_encode(sha1(mt_rand())), 0, $this->algorithm->getEffectiveSaltSize());
		}
		return $salt;
	}
	
	public function encrypt($str) {
		return crypt($str, $this->algorithm->generateSaltPatternForSalt($this->salt));
	}
	/**
	 * @return \n2n\util\crypt\hash\algorithm\HashAlgorithm
	 */
	public function getAlgorithm() {
		return $this->algorithm;
	}
	/**
	 * @param \n2n\util\crypt\hash\algorithm\HashAlgorithm $algorithm
	 */
	public function setAlgorithm(HashAlgorithm $algorithm) {
		$this->algorithm = $algorithm;
	}
	/**
	 * @param string $salt
	 */
	public function setSalt($salt) {
		$this->salt = $salt;
	}
	/**
	 * @return string
	 */
	public function getSalt() {
		return $this->salt;
	}
	
	public static function compare($raw, $hash) {
		return $hash == crypt((string) $raw, $hash);
	}
	
	private function getSaltRegExp() {
		return str_replace(self::PLACEHOLDER_SALT_LENGTH, $this->algorithm->getEffectiveSaltSize(), self::REG_EXP_SALT);
	}
}
