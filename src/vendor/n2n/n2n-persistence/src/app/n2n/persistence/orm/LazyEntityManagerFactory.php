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
namespace n2n\persistence\orm;

use n2n\core\container\PdoPool;
use n2n\util\ex\IllegalStateException;

class LazyEntityManagerFactory implements EntityManagerFactory {
	private $persistenceUnitName;
	private $pdoPool;
	
	private $temc;
	private $shared;
	private $transactionalEm;
	
	/**
	 * @param string|null $persistenceUnitName
	 * @param PdoPool $pdoPool
	 */
	public function __construct(string $persistenceUnitName = null, PdoPool $pdoPool) {
		$this->persistenceUnitName = $persistenceUnitName;
		$this->pdoPool = $pdoPool;	
	}
	
	/**
	 * {@inheritDoc}
	 * @see \n2n\persistence\orm\EntityManagerFactory::getTransactional()
	 */
	public function getTransactional() {
		if ($this->transactionalEm !== null && $this->transactionalEm->isOpen()) {
			return $this->transactionalEm;
		}
		
		$pdo = $this->pdoPool->getPdo($this->persistenceUnitName);
		if (!$pdo->inTransaction()) {
			throw new IllegalStateException('No tranaction open.');
		}
		
		$this->transactionalEm = new LazyEntityManager($this->persistenceUnitName, $this->pdoPool, true);
		$this->transactionalEm->bindPdo($pdo);
	
		return $this->transactionalEm;
	}	
	
	/**
	 * {@inheritDoc}
	 * @see \n2n\persistence\orm\EntityManagerFactory::getExtended()
	 */
	public function getExtended() {
		if (!isset($this->shared)) {
			$this->shared = $this->create();
		}
		return $this->shared;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \n2n\persistence\orm\EntityManagerFactory::create()
	 */
	public function create() {
		return new LazyEntityManager($this->persistenceUnitName, $this->pdoPool, false);
	}
}

// class TransactionalEmContainer implements PdoListener {
// 	private $em;
	
// 	public function __construct(EntityManager $em) {
// 		$dbh = $em->getPdo();
// 		if (!$dbh->inTransaction()) {
// 			throw new ContainerConflictException(SysTextUtils::get('n2n_error_persitence_orm_no_transaction_active'));
// 		}
// 		$dbh->registerListener($this);
		
// 		$this->em = $em;
// 	}
	
// 	public function isAvailable() {
// 		return isset($this->em);
// 	}
	
// 	public function getEntityManager() {
// 		return $this->em;
// 	}
	
// 	public function onTransactionEvent(TransactionEvent $e) {
// 		if ($e->getType() == TransactionEvent::TYPE_COMMITTED 
// 				|| $e->getType() == TransactionEvent::TYPE_ROLLED_BACK) {
// 			if ($this->isAvailable()) {
// 				$this->em->close();
// 				$this->em = null;
// 			}
// 		}
// 	}
// }
