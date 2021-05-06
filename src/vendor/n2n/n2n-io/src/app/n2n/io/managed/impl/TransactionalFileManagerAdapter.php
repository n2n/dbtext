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
namespace n2n\io\managed\impl;

use n2n\core\container\TransactionManager;
use n2n\util\ex\IllegalStateException;
use n2n\io\managed\File;
use n2n\io\managed\FileLocator;
use n2n\io\IncompatibleFileException;
use n2n\core\container\Transaction;
use n2n\context\Lookupable;
use n2n\core\container\TransactionalResource;
use n2n\io\managed\FileManager;
use n2n\core\container\CommitListener;
use n2n\core\container\CommitFailedException;
use n2n\io\managed\impl\engine\transactional\TransactionalFileEngine;

abstract class TransactionalFileManagerAdapter implements FileManager, Lookupable, TransactionalResource, CommitListener {
	protected $tm;
	/**
	 * @var TransactionalFileEngine
	 */
	protected $fileEngine;
	
	private function _init(TransactionManager $tm) {
		$this->tm = $tm;
		
		$tm->registerResource($this);
		$tm->registerCommitListener($this);
	}
	
	/**
	 * @throws IllegalStateException
	 * @return \n2n\io\managed\impl\engine\transactional\TransactionalFileEngine
	 */
	private function getFileEngine() {
		if ($this->fileEngine === null) {
			throw new IllegalStateException('FileManager not initialized.');
		}
	
		return $this->fileEngine;
	}
	
	private function ensureNotReadOnly($operationName) {
		if (true === $this->tm->isReadyOnly()) {
			throw new IllegalStateException($operationName . ' operation disallowed in ready only transaction.');
		}
	}
	/* (non-PHPdoc)
	 * @see \n2n\io\managed\FileManager::persist()
	 */
	public function persist(File $file, FileLocator $fileLocator = null): string {
		$this->ensureNotReadOnly('persist');
	
		$fileEngine = $this->getFileEngine();
		$qualifiedName = $fileEngine->persist($file, $fileLocator);
		if (!$this->tm->hasOpenTransaction()) {
			$fileEngine->flush();
		}
	
		return $qualifiedName;
	}
	/* (non-PHPdoc)
	 * @see \n2n\io\managed\FileManager::removeByQualifiedName()
	 */
	public function removeByQualifiedName($qualifiedName) {
		$this->ensureNotReadOnly('remove');
	
		$fileEngine = $this->getFileEngine();
		$fileEngine->removeByQualifiedName($qualifiedName);
		if (!$this->tm->hasOpenTransaction()) {
			$fileEngine->flush();
		}
	}
	
	public function remove(File $file) {
		$this->ensureNotReadOnly('remove');
	
		$fileEngine = $this->getFileEngine();
		if (!$fileEngine->containsFile($file)) {
			throw new IncompatibleFileException('File is not managed by ' . get_class($this) . ': '
					. $file->__toString());
		}
	
		$fileEngine->remove($file);
		if (!$this->tm->hasOpenTransaction()) {
			$this->fileEngine->flush();
		}
	}
	/* (non-PHPdoc)
	 * @see \n2n\io\managed\FileManager::clear()
	 */
	public function clear() {
		$fileEngine = $this->getFileEngine();
		$fileEngine->removeAll();
		if (!$this->tm->hasOpenTransaction()) {
			$fileEngine->flush();
		}
	}
	/* (non-PHPdoc)
	 * @see \n2n\io\managed\FileManager::checkFile()
	 */
	public function checkFile(File $file) {
		return $this->getFileEngine()->checkFile($file);
	}
	/* (non-PHPdoc)
	 * @see \n2n\io\managed\FileManager::getByQualifiedName()
	 */
	public function getByQualifiedName(string $qualifiedName = null) {
		if ($qualifiedName === null) return null;
		
		return $this->getFileEngine()->getByQualifiedName($qualifiedName);
	}
	/* (non-PHPdoc)
	 * @see \n2n\core\container\TransactionalResource::beginTransaction()
	 */
	public function beginTransaction(Transaction $transaction) {}
	/* (non-PHPdoc)
	 * @see \n2n\core\container\TransactionalResource::prepareCommit()
	 */
	public function prepareCommit(Transaction $transaction): bool {
		return true;
	}
	/* (non-PHPdoc)
	 * @see \n2n\core\container\TransactionalResource::commit()
	 */
	public function commit(Transaction $transaction) {}
	/* (non-PHPdoc)
	 * @see \n2n\core\container\TransactionalResource::rollBack()
	 */
	public function rollBack(Transaction $transaction) {
		$this->fileEngine->clearBuffer();
	}
	
	public function preCommit(Transaction $transaction) {
		$this->fileEngine->flush(true);
	}

	public function commitFailed(Transaction $transaction, CommitFailedException $e) {
		$this->fileEngine->abortFlush();
	}
	
	public function postCommit(Transaction $transaction) {
		$this->fileEngine->flush();
	}
	
	function hasThumbSupport(): bool {
		return true;
	}
	
	function getPossibleImageDimensions(File $file, FileLocator $fileLocator = null): array {
		return $this->fileEngine->getPossibleImageDimensions($file, $fileLocator);
	}
}
