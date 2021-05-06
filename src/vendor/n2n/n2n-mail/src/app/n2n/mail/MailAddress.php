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
namespace n2n\mail;

use n2n\util\type\ArgUtils;

class MailAddress {
	private $email;
	private $name;
	
	public function __construct($address) {
		// check if e-mail only
		if (filter_var($address, FILTER_VALIDATE_EMAIL)) {
			$this->email = $address;
			return;
		}
		$this->validateAddress($address);
		$this->email = $this->stripEmail($address);
		$this->name = $this->stripName($address);
	}
	
	public function getEmail() {
		return $this->email;
	}
	
	/**
	 * returns the name, if desired in quoted style for e-mail header output
	 * 
	 * @param bool $quoted
	 */
	public function getName($quoted = false) {
		if (!$quoted) return $this->name;
		
		$quotedName = addcslashes($this->name, "\0..\37\177\\\"");
		// check if name needs to be quoted
		if ($this->name == $quotedName && !preg_match('/[^A-Za-z0-9!#$%&\'*+\/=?^_`{|}~ -]/', $this->name)) {
			return $this->name;
		} else {
			return mb_encode_mimeheader($quotedName, 'utf-8', 'Q', "\r\n");
			// return "\"{$quotedName}\"";
		}
	}
	
	/**
	 * checks if an address contains a valid e-mail address
	 * 
	 * @param string $address
	 * @throws MailFormatException
	 */
	private function validateAddress($address) {
		ArgUtils::valType($address, 'scalar');
		
		$email = self::stripEmail($address);
		
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) throw new MailFormatException('invalid e-mail address: ' . $address);
	}
	
	/**
	 * returns the e-mail from an expression like 'John Doe <john@doe.com>' -> john@doe.com
	 * 
	 * @param string $email
	 * @return string
	 */
	public static function stripEmail($email) {
		if (strpos($email, '<') && substr($email, -1) == '>') {
			return substr($email, strpos($email, '<') + 1, (strlen($email) - strpos($email, '<')) - 2);
		}
		return $email;
	}
	
	/**
	 * returns the name of an expression like 'John Doe <john@doe.com>' -> John Doe
	 * @param string $address
	 * @return string
	 */
	public static function stripName($address) {
		// strip line breaks and trim
		$address = trim(preg_replace('/[\r\n]+/', '', $address));
		
		$email = self::stripEmail($address);
		$name = trim(str_replace('<' . $email . '>', '', $address));
		// check if quotes need to be removed
		if (substr($name, 0, 1) == "\"" && substr($name, -1) == "\"") {
			$name = substr($name, 1, strlen($name) - 2);
		}
		return $name;
	}
	
	public function __toString(): string {
		if (empty($this->name)) return $this->email;
		return "{$this->getName(true)} <{$this->email}>";
	}
}
