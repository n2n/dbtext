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

use n2n\persistence\meta\MetaData;
use n2n\reflection\ReflectionUtils;
use n2n\core\container\TransactionManager;
use n2n\core\container\Transaction;
use n2n\core\container\CommitFailedException;

class Pdo extends \PDO {
	private $dataSourceName;
	private $entityManager;
	private $dialect;
	private $logger;
	private $metaData;
	private $transactionManager;
	private $listeners = array();
	
	public function __construct(PersistenceUnitConfig $persistenceUnitConfig, TransactionManager $transactionManager = null) {
		try {
			parent::__construct($persistenceUnitConfig->getDsnUri(), $persistenceUnitConfig->getUser(),
					$persistenceUnitConfig->getPassword(), array(\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
							\PDO::ATTR_STATEMENT_CLASS => array('n2n\persistence\PdoStatement', array())));
		} catch (\PDOException $e) {
			throw new PdoException($e);
		}
		$this->dataSourceName = $persistenceUnitConfig->getName();
		$this->logger = new PdoLogger($this->dataSourceName);
		
		$dialectClass = ReflectionUtils::createReflectionClass($persistenceUnitConfig->getDialectClassName());
		if (!$dialectClass->implementsInterface('n2n\\persistence\\meta\\Dialect')) {
			throw new \InvalidArgumentException(
					'Dialect class must implement n2n\\persistence\\meta\\Dialect: ' 
							. $dialectClass->getName());
		}
		$this->dialect = $dialectClass->newInstance();
		$this->dialect->initializeConnection($this, $persistenceUnitConfig);
		$this->metaData = new MetaData($this, $this->dialect);
		
		$this->transactionManager = $transactionManager;
		
		if ($transactionManager !== null) {
			$that = $this;
			$transactionManager->registerResource(new PdoTransactionalResource(
					function (Transaction $transaction) use ($that) { $that->performBeginTransaction($transaction); },
					function (Transaction $transaction) use ($that) { return $that->prepareCommit($transaction); },
					function (Transaction $transaction) use ($that) {
						try {
							$that->performCommit($transaction);
						} catch (PdoCommitException $e) {
							throw new CommitFailedException('Pdo commit failed. Reason: ' . $e->getMessage(), 0, $e);
						}
					},
					function (Transaction $transaction) use ($that) { $that->performRollBack($transaction); }));
		}
	}
	/**
	 * @return TransactionManager
	 */
	public function getTransactionManager() {
		return $this->transactionManager;
	}
	/**
	 * @return string
	 */
	public function getDataSourceName() {
		return $this->dataSourceName;
	}
	/**
	 * @return \n2n\persistence\PdoLogger
	 */
	public function getLogger() {
		return $this->logger;
	}
	/**
	 *
	 * @param object $pdo
	 * @return boolean
	 */
	public function equals($dbh) {
		if (!($dbh instanceof Pdo)) return false;

		return $this->dataSourceName == $dbh->getDataSourcename();
	}
	/**
	 *
	 * @return PdoStatement
	 */
	public function prepare($statement, $driverOptions = array()) {
		try {
			$mtime = microtime(true);
				
			$stmt = parent::prepare($statement, $driverOptions);
			
			$this->logger->addPreparation($statement, (microtime(true) - $mtime));
			$stmt->setLogger($this->logger);
			
			return $stmt;
		} catch (PDOException $e) {
			throw new PdoStatementException($e, $statement);
		}
	}

	public function query(string $statement, ?int $fetchMode = null, ...$fetchModeArgs) {
		try {
			$mtime = microtime(true);
			$query = parent::query($statement, $fetchMode, $fetchModeArgs);
			$this->logger->addQuery($statement, (microtime(true) - $mtime));
			return $query;
		} catch (\PDOException $e) {
			throw new PdoStatementException($e, $statement);
		}
	}
	/**
	 *
	 * @return int
	 */
	public function exec($statement) {
		try {
			$mtime = microtime(true);
			$stmt = parent::exec($statement);
			$this->logger->addExecution($statement, (microtime(true) - $mtime));
			return $stmt;
		} catch (\PDOException $e) {
			throw new PdoStatementException($e, $statement);
		}
	}
	/**
	 * (non-PHPdoc)
	 * @see PDO::beginTransaction()
	 */
	public function beginTransaction() {
		if ($this->transactionManager === null) {
			$this->performBeginTransaction();
			return;
		}
		
		if (!$this->transactionManager->hasOpenTransaction()) {
			$this->transactionManager->createTransaction();
		}
	}
	/**
	 * (non-PHPdoc)
	 * @see PDO::commit()
	 */
	public function commit() {
		if ($this->transactionManager === null) {
			$this->prepareCommit();
			$this->performCommit();
		}
		
		if ($this->transactionManager->hasOpenTransaction()) {
			$this->transactionManager->getRootTransaction()->commit();
		}
	}
	/**
	 * (non-PHPdoc)
	 * @see PDO::rollBack()
	 */
	public function rollBack() {
		if ($this->transactionManager === null) {
			$this->performRollBack();
			return;
		}
		
		if ($this->transactionManager->hasOpenTransaction()) {
			$this->transactionManager->getRootTransaction()->rollBack();
		}
	}
	
	
	private function performBeginTransaction(Transaction $transaction = null) {
		$this->triggerTransactionEvent(TransactionEvent::TYPE_ON_BEGIN, $transaction);
		$mtime = microtime(true);
		parent::beginTransaction();
		$this->logger->addTransactionBegin(microtime(true) - $mtime);
		$this->triggerTransactionEvent(TransactionEvent::TYPE_BEGAN, $transaction);
	}
	
	private function prepareCommit(Transaction $transaction = null) {
		$this->triggerTransactionEvent(TransactionEvent::TYPE_ON_COMMIT, $transaction);
		return true;
	}
	
	private function performCommit(Transaction $transaction = null) {
		$mtime = microtime(true);
		
		$preErr = error_get_last();
		$result = @parent::commit();
		$postErr = error_get_last();
		
		// Problem: Warining: Error while sending QUERY packet. PID=223316 --> parent::commit() will return true but 
		// triggers warning. 
		// http://php.net/manual/de/pdo.transactions.php
		if (!$result || $preErr !== $postErr) {
			throw new PdoCommitException($postErr['message'] ?? 'Commit failed due to unknown reason.');
		}
		
		$this->logger->addTransactionCommit(microtime(true) - $mtime);
		$this->triggerTransactionEvent(TransactionEvent::TYPE_COMMITTED, $transaction);
	}
	
	private function performRollBack(Transaction $transaction = null) {
		$this->triggerTransactionEvent(TransactionEvent::TYPE_ON_ROLL_BACK, $transaction);
		$mtime = microtime(true);
		parent::rollBack();
		$this->logger->addTransactionRollBack(microtime(true) - $mtime);
		$this->triggerTransactionEvent(TransactionEvent::TYPE_ROLLED_BACK, $transaction);
	}
	/**
	 *
	 * @param string $field
	 */
	public function quoteField($field) {
		return $this->dialect->quoteField($field);
	}
// 	/**
// 	 *
// 	 */
// 	public function dumpLog() {
// 		if(isset($this->log)) $this->log->dump();
// 	}

	/**
	 * @return \n2n\persistence\meta\MetaData
	 */
	public function getMetaData() {
		return $this->metaData;
	}
	
	private function triggerTransactionEvent($type, Transaction $transaction = null) {
		$e = new TransactionEvent($type, $transaction);
		foreach ($this->listeners as $listener) {
			$listener->onTransactionEvent($e);
		}
	}
	/**
	 * @param PdoListener $listener
	 */
	public function registerListener(PdoListener $listener) {
		$this->listeners[spl_object_hash($listener)] = $listener;
	}
	/**
	 * @param PdoListener $listener
	 */
	public function unregisterListener(PdoListener $listener) {
		unset($this->listeners[spl_object_hash($listener)]);
	}
}

class TransactionEvent {
	const TYPE_ON_BEGIN = 'begin';
	const TYPE_BEGAN = 'began';
	const TYPE_ON_COMMIT = 'onCommit';
	const TYPE_COMMITTED = 'committed';
	const TYPE_ON_ROLL_BACK = 'onRollback';
	const TYPE_ROLLED_BACK = 'rollBacked';
	
	private $type;
	private $transaction;
	
	public function __construct($type, Transaction $transaction = null) {
		$this->type = $type;
		$this->transaction = $transaction;
	}
	
	public function getType() {
		return $this->type;
	}
	/**
	 * @return Transaction
	 */
	public function getTransaction() {
		return $this->transaction;
	}
}
