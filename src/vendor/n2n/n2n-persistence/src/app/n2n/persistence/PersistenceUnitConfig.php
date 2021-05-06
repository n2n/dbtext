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
namespace n2n\persistence;

class PersistenceUnitConfig {
	const TIL_READ_UNCOMMITTED = "READ UNCOMMITTED";
	const TIL_READ_COMMITTED = "READ COMMITTED";
	const TIL_REPEATABLE_READ = "REPEATABLE READ";
	const TIL_SERIALIZABLE = "SERIALIZABLE";
	
	private $name;
	private $dsnUri;
	private $user;
	private $password;
	private $transactionIsolationLevel;
	private $dialectClassName;
	
	public function __construct($name, $dsnUri, $user, $password, $transactionIsolationLevel, $dialectClassName) {
		$this->name = $name;
		$this->dsnUri = $dsnUri;
		$this->user = $user;
		$this->password = $password;
		$this->transactionIsolationLevel = $transactionIsolationLevel;
		$this->dialectClassName = $dialectClassName;
	}
	
	public function getName() {
		return $this->name;
	}
	
	public function getDsnUri() {
		return $this->dsnUri;
	}
	
	public function getUser() {
		return $this->user;		
	}
	
	public function getPassword() {
		return $this->password;
	}
	
	public function getTransactionIsolationLevel() {
		return $this->transactionIsolationLevel;
	}
	
	public function getDialectClassName() {
		return $this->dialectClassName;
	}
	
	public static function getTransactionIsolationLevels() {
		return array(self::TIL_READ_UNCOMMITTED, self::TIL_READ_COMMITTED, 
				self::TIL_REPEATABLE_READ, self::TIL_SERIALIZABLE);
	}
}
