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
// namespace n2n\util\cache\impl;

// use n2n\core\container\TransactionManager;
// use n2n\util\cache\CacheStore;
// use n2n\core\container\TransactionalResource;
// use n2n\core\container\Transaction;

// class TransactionalCacheStore implements CacheStore {
// 	private $transactionManager;
// 	private $cacheStore;
	
// 	public function __construct(TransactionManager $transactionManager, CacheStore $cacheStore) {
// 		$this->transactionManager = $transactionManager->registerResource($this);
// 		$this->cacheStore = $cacheStore;
// 	}
// 	/**
// 	 * {@inheritDoc}
// 	 * @see \n2n\util\cache\CacheStore::store()
// 	 */
// 	public function store(string $name, array $characteristics, $data, \DateTime $lastMod = null) {
// 		return $this->cacheStore->get($name, $characteristics);
// 	}

// 	/**
// 	 * {@inheritDoc}
// 	 * @see \n2n\util\cache\CacheStore::get()
// 	 */
// 	public function get(string $name, array $characteristics) {
// 		return $this->cacheStore->get($name, $characteristics);
// 	}

// 	/**
// 	 * {@inheritDoc}
// 	 * @see \n2n\util\cache\CacheStostringublic function remove(string $name, array $characteristics) {
// 		// TODO Auto-generated method stub
		
// 	}

// 	/**
// 	 * {@inheritDoc}
// 	 * @see \n2n\util\cache\CacheStore::findAll()
// 	 */
// 	public function findAll(string $name, array $characteristicNeedles = null) {
// 		return $this->cacheStore->findAll($name, $characteristicNeedles);
// 	}

// 	/**
// 	 * {@inheritDoc}
// 	 * @see \n2n\util\cache\CacheStore::removeAll()
// 	 */
// 	public function removeAll(string $name, array $characteristicNeedles = null) {
// 		// TODO Auto-generated method stub
		
// 	}

// 	/**
// 	 * {@inheritDoc}
// 	 * @see \n2n\util\cache\CacheStore::clear()
// 	 */
// 	public function clear() {
// 		// TODO Auto-generated method stub
		
// 	}

	
	
// }


// class TransactionalCacheStoreResource implements TransactionalResource {
	
	
	
// 	/**
// 	 * {@inheritDoc}
// 	 * @see \n2n\core\container\TransactionalResource::beginTransaction()
// 	 */
// 	public function beginTransaction(Transaction $transaction) {
		
// 	}

// 	/**
// 	 * {@inheritDoc}
// 	 * @see \n2n\core\container\TransactionalResource::prepareCommit()
// 	 */
// 	public function prepareCommit(Transaction $transaction) {
		
// 	}

// 	/**
// 	 * {@inheritDoc}
// 	 * @see \n2n\core\container\TransactionalResource::commit()
// 	 */
// 	public function commit(Transaction $transaction) {
		
// 	}

// 	/**
// 	 * {@inheritDoc}
// 	 * @see \n2n\core\container\TransactionalResource::rollBack()
// 	 */
// 	public function rollBack(Transaction $transaction) {
		
// 	}

	
// }
