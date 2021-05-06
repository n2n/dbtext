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

class PdoStatement extends \PDOStatement {
	private $logger;
	private $boundValues = array();

	private function __construct() {
	}
	
	public function getBindedValues() {
		return $this->boundValues;
	}
	
	/**
	 * @param PdoLogger $logger
	 */
	public function setLogger(PdoLogger $logger) {
		$this->logger = $logger;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see PDOStatement::bindValue()
	 */
	public function bindValue($parameter, $value, $dataType = null) {
		$this->boundValues[$parameter] = $value;
		if ($dataType !== null) {
			return parent::bindValue($parameter, $value, $dataType);
		} else {
			return parent::bindValue($parameter, $value);
		}
	}
	
	public function autoBindValue($parameter, $value) {
		$dataType = null;
		if (is_int($value)) {
			$dataType = PDO::PARAM_INT;
		} else if (is_bool($value)) {
			$dataType = PDO::PARAM_BOOL;
		} else {
			$dataType = PDO::PARAM_STR;
		}
		return $this->bindValue($parameter, $value, $dataType);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see PDOStatement::bindParam()
	 */
	public function bindParam($parameter, &$variable, $data_type = PDO::PARAM_STR, $length = null, $driver_options = null) {
		$this->boundValues[$parameter] = $variable;
		return parent::bindParam($parameter, $variable, $data_type, $length, $driver_options);
	}
	
	private $boundParams = array();
	private $shareBoundParams = array();
	private $test;
	
	public function shareBindColumn($column, &$param) {
		if (!isset($this->shareBoundParams[$column])) {
			$this->shareBoundParams[$column] = array();
			
			$this->boundParams[$column] = null;
			$this->bindColumn($column, $this->boundParams[$column]);
		}
		
		$this->shareBoundParams[$column][] = &$param;
	}
	
	private function supplySharedBounds() {
		foreach ($this->boundParams as $columnName => $param) {
			foreach ($this->shareBoundParams[$columnName] as $key => $value) {
				$this->shareBoundParams[$columnName][$key] = $param;
			}
		}
	}
	/**
	 * (non-PHPdoc)
	 * @see PDOStatement::execute()
	 */
	public function execute($input_parameters = null) {
		if (is_array($input_parameters)) $this->boundValues = $input_parameters;
		
		try {
			$mtime = microtime(true);
			$return = parent::execute($input_parameters);
			if (isset($this->logger)) {
				$this->logger->addPreparedExecution($this->queryString, $this->boundValues, (microtime(true) - $mtime));
			}
			
			if (!$return) {
				$err = error_get_last();
				throw new \PDOException($err['message']);
			}
			
			return $return;
		} catch (\PDOException $e) {
			throw new PdoPreparedExecutionException($e, $this->queryString, $this->boundValues);
		}
	}
	
	public function registerListener(PdoStatementListener $listener) {
		$this->listeners[spl_object_hash($listener)] = $listener;
	}
	
	public function unregisterListener(PdoStatementListener $listener) {
		unset($this->listeners[spl_object_hash($listener)]);
	}
	

	public function fetch($fetch_style = null, $cursor_orientation = null, $cursor_offset = null) {
		$return = parent::fetch($fetch_style, $cursor_orientation, $cursor_offset);
		
		if ($fetch_style == PDO::FETCH_BOUND) {
			$this->supplySharedBounds();
		}
		
		return $return;
	}
}
