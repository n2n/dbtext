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

class PdoLogger {
	private $dsName;
	private $log;
	/**
	 *
	 * @param string $dataSourceName
	 */
	public function __construct($dataSourceName) {
		$this->dsName = $dataSourceName;
		$this->log = array();
	}
	
	public function clear() {
		$this->log = array();
	}
	
	public function getLog() {
		return $this->log;
	}
	/**
	 *
	 * @param string $sqlStr
	 * @param number $time
	 */
	public function addQuery($sqlStr, $time = null) {
		$this->log[] = array('sql' => $sqlStr, 'type' => 'query', 'time' => $time);
// 		test(end($this->log));
	}
	/**
	 *
	 * @param string $sqlStr
	 * @param number $time
	 */
	public function addExecution($sqlStr, $time = null) {
		$this->log[] = array('sql' => $sqlStr, 'type' => 'execute', 'time' => $time);
// 		test(end($this->log));
	}
	/**
	 *
	 * @param string $sqlStr
	 * @param number $time
	 */
	public function addPreparation($sqlStr, $time = null) {
		$this->log[] = array('sql' => $sqlStr, 'type' => 'prepare', 'time' => $time);
// 		test(end($this->log));
	}
	/**
	 *
	 * @param string $sqlStr
	 * @param array $values
	 * @param number $time
	 */
	public function addPreparedExecution($sqlStr, array $values = null, $time = null) {
		$this->log[] = array('sql' => $sqlStr, 'values' => $values, 'type' => 'prepared-execute', 'time' => $time);
// 		test(end($this->log));
	}
	/**
	 *
	 * @param number $time
	 */
	public function addTransactionBegin($time = null) {
		$this->log[] = array('type' => 'begin transaction', 'time' => $time);
// 		test(end($this->log));
	}
	/**
	 *
	 * @param number $time
	 */
	public function addTransactionRollBack($time = null) {
		$this->log[] = array('type' => 'rollback', 'time' => $time);
// 		test(end($this->log));
	}
	/**
	 *
	 * @param number $time
	 */
	public function addTransactionCommit($time = null) {
		$this->log[] = array('type' => 'commit', 'time' => $time);
// 		test(end($this->log));
	}
	/**
	 * @return array
	 */
	public function getEntries() {
		return $this->log;
	}
	/**
	 * 
	 */
	public function dump() {
		test($this->log);
	}
}
