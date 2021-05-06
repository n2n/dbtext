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
namespace n2n\util\cache\impl;

use n2n\util\cache\CacheStore;
use n2n\io\fs\FsPath;
use n2n\io\IoUtils;
use n2n\util\ex\IllegalStateException;
use n2n\util\HashUtils;
use n2n\util\cache\CacheItem;
use n2n\util\StringUtils;
use n2n\util\UnserializationFailedException;
use n2n\util\cache\CorruptedCacheStoreException;
use n2n\io\IoException;
use n2n\util\DateUtils;
use n2n\io\fs\FileResourceStream;

class FileCacheStore implements CacheStore {
	const CHARACTERISTIC_DELIMITER = '.';
	const CHARACTERISTIC_HASH_LENGTH = 4;
	const CACHE_FILE_SUFFIX = '.cache';
	const LOCK_FILE_SUFFIX = '.lock';
	
	private $dirPath;
	private $dirPerm;
	private $filePerm;
	/**
	 * @param mixed $dirPath
	 * @param string $dirPerm
	 * @param string $filePerm
	 */
	public function __construct($dirPath, $dirPerm = null, $filePerm = null) {
		$this->dirPath = new FsPath($dirPath);
		$this->dirPerm = $dirPerm;
		$this->filePerm = $filePerm;
	}
	/**
	 * @return \n2n\io\fs\FsPath
	 */
	public function getDirPath() {
		return $this->dirPath;
	}
	/**
	 * @param string $dirPerm
	 */
	public function setDirPerm($dirPerm) {
		$this->dirPerm = $dirPerm;
	}
	/**
	 * @return string
	 */
	public function getDirPerm() {
		return $this->dirPerm;
	}
	/**
	 * @param string $filePerm
	 */
	public function setFilePerm($filePerm) {
		$this->filePerm = $filePerm;
	}
	/**
	 * @return string
	 */
	public function getFilePerm() {
		return $this->filePerm;
	}
	/**
	 * @param string $filePath
	 * @return \n2n\util\cache\impl\CacheFileLock
	 */
	private function createReadLock(string $filePath) {
		return new CacheFileLock(new FileResourceStream($filePath . self::LOCK_FILE_SUFFIX, 'w', LOCK_SH));
	}
	/**
	 * @param string $filePath
	 * @return \n2n\util\cache\impl\CacheFileLock
	 */
	private function createWriteLock(string $filePath) {
		return new CacheFileLock(new FileResourceStream($filePath . self::LOCK_FILE_SUFFIX, 'w', LOCK_EX));
	}
	
	private function buildNameDirPath($name) {
		if (IoUtils::hasSpecialChars($name)) {
			$name = HashUtils::base36Md5Hash($name);
		}
		
		return $this->dirPath->ext($name);
	}
	
	private function buildFileName(array $characteristics) {
		ksort($characteristics);
		
		$fileName = HashUtils::base36Md5Hash(serialize($characteristics));
		foreach ($characteristics as $key => $value) {
			$fileName .= self::CHARACTERISTIC_DELIMITER . HashUtils::base36Md5Hash(
					serialize(array($key, $value)), self::CHARACTERISTIC_HASH_LENGTH); 
		}
		
		return $fileName . self::CACHE_FILE_SUFFIX;		
	}
	
	private function buildGlobPattern(array $characteristics) {
		ksort($characteristics);
	
		$fileName = HashUtils::base36Md5Hash(serialize($characteristics));
		foreach ($characteristics as $key => $value) {
			$fileName .= '*' . self::CHARACTERISTIC_DELIMITER . HashUtils::base36Md5Hash(
					serialize(array($key, $value)), self::CHARACTERISTIC_HASH_LENGTH);
		}
	
		return '*' . self::CACHE_FILE_SUFFIX;
	}
	/* (non-PHPdoc)
	 * @see \n2n\util\cache\CacheStore::store()
	 */
	public function store(string $name, array $characteristics, $data, \DateTime $lastMod = null) {
		$nameDirPath = $this->buildNameDirPath($name);
		if (!$nameDirPath->isDir()) {
			if ($this->dirPerm === null) {
				throw new IllegalStateException('No directory permission set for FileCacheStore.');
			}
			
			$nameDirPath->mkdirs($this->dirPerm);
		}
		
		if ($this->filePerm === null) {
			throw new IllegalStateException('No file permission set for FileCacheStore.');
		}
		
		if ($lastMod === null) {
			$lastMod = new \DateTime();
		}
		
		$filePath = $nameDirPath->ext($this->buildFileName($characteristics));
				
		$lock = $this->createWriteLock((string) $filePath);
		IoUtils::putContentsSafe($filePath->__toString(), serialize(array('characteristics' => $characteristics, 
				'data' => $data, 'lastMod' => $lastMod->getTimestamp())));
		
		
		$filePath->chmod($this->filePerm);
		$lock->release();
	}
	/**
	 * @param $name
	 * @param FsPath $filePath
	 * @return CacheItem null, if filePath no longer available.
	 * @throws CorruptedCacheStoreException
	 */
	private function read($name, FsPath $filePath) {
		if (!$filePath->exists()) return null;
		
		$lock = $this->createReadLock($filePath);
		if (!$filePath->exists()) {
			$lock->release(true);
			return null;
		}
		
		$contents = null;
		try {
			$contents = IoUtils::getContentsSafe($filePath);
		} catch (IoException $e) {
			$lock->release();
			return null;
		}
		$lock->release();
		
		// file could be empty due to writing anomalies
		if (empty($contents)) {
			return null;
		}
		
		$attrs = null;
		try {
// 			$time_start = microtime(true);
			$attrs = StringUtils::unserialize($contents);
// 			$time_end = microtime(true);
// 			test($time_end - $time_start);
		} catch (UnserializationFailedException $e) {
			throw new CorruptedCacheStoreException('Could not retrive file: ' . $filePath, 0, $e);
		}
		
		if (!isset($attrs['characteristics']) || !is_array($attrs['characteristics']) || !isset($attrs['data'])
				|| !isset($attrs['data']) || !isset($attrs['lastMod']) || !is_numeric($attrs['lastMod'])) {
			throw new CorruptedCacheStoreException('Corrupted cache file: ' . $filePath);
		}


		$ci = new CacheItem($name, $attrs['characteristics'], null, 
				DateUtils::createDateTimeFromTimestamp($attrs['lastMod']));
		$ci->data = &$attrs['data'];
		return $ci;
	}
	/* (non-PHPdoc)
	 * @see \n2n\util\cache\CacheStore::get()
	 */
	public function get(string $name, array $characteristics) {
		$nameDirPath = $this->buildNameDirPath($name);
		if (!$nameDirPath->exists()) return null;
		return $this->read($name, $nameDirPath->ext($this->buildFileName($characteristics)));
	}
	/* (non-PHPdoc)
	 * @see \n2n\util\cache\CacheStore::remove()
	 */
	public function remove(string $name, array $characteristics) {
		$nameDirPath = $this->buildNameDirPath($name);
		if (!$nameDirPath->exists()) return;

		$filePath = $nameDirPath->ext($this->buildFileName($characteristics));
		$this->unlink($filePath);		
	}
	/**
	 * @param FsPath $filePath
	 */
	private function unlink(FsPath $filePath) {
		if (!$filePath->exists()) return;
		
		$lock = $this->createWriteLock($filePath);
		
		if ($filePath->exists())  {
			try {
				IoUtils::unlink($filePath->__toString());
			} catch (IoException $e) {
				$lock->release(true);
				throw $e;
			}
		}
		
		$lock->release(true);
	}
	/**
	 * @param array $characteristics
	 * @return boolean
	 */
	private function inCharacteristics(array $characteristicNeedles, array $characteristics) {
		foreach ($characteristicNeedles as $key => $value) {
			if (!array_key_exists($key, $characteristics)  
					|| $value !== $characteristics[$key]) return false;
		}
		
		return true;
	}
	/**
	 * @param string $name
	 * @param array $characteristicNeedles
	 * @return FsPath[]
	 */
	private function findFilePaths($name, array $characteristicNeedles = null) {
		$filePaths = array();
		
		$nameDirPath = $this->buildNameDirPath($name);
		if (!$nameDirPath->exists()) {
			return $filePaths;
		}
		
		return $nameDirPath->getChildren($this->buildGlobPattern((array) $characteristicNeedles));
		
	}
	/* (non-PHPdoc)
	 * @see \n2n\util\cache\CacheStore::findAll()
	 */
	public function findAll(string $name, array $characteristicNeedles = null) {
		$cacheItems = array();
		
		foreach ($this->findFilePaths($name, $characteristicNeedles) as $filePath) {
			$cacheItem = $this->read($name, $filePath);
			if ($cacheItem === null) continue;
			
			if ($characteristicNeedles === null 
					|| $this->inCharacteristics($characteristicNeedles, $cacheItem->getCharacteristics())) {
				$cacheItems[] = $cacheItem;
			}
		}
		
		return $cacheItems;
	}
	/* (non-PHPdoc)
	 * @see \n2n\util\cache\CacheStore::removeAll()
	 */
	public function removeAll(string $name, array $characteristicNeedles = null) {
		foreach ($this->findFilePaths($name, $characteristicNeedles) as $filePath) {
			if (empty($characteristicNeedles)) {
				$this->unlink($filePath);
				continue;
			}
			
			$cacheItem = $this->read($name, $filePath);
			if ($cacheItem === null) continue;
			
			if ($characteristicNeedles === null 
					|| $this->inCharacteristics($characteristicNeedles, $cacheItem->getCharacteristics())) {
				$this->unlink($filePath);
			}
		}
	}
	/* (non-PHPdoc)
	 * @see \n2n\util\cache\CacheStore::clear()
	 */
	public function clear() {
		foreach ($this->dirPath->getChildDirectories() as $nameDirPath) {
			$this->removeAll($nameDirPath->getName());	
		}
	}
}

class CacheFileLock {	
	private $frs;
	/**
	 * @param FileResourceStream $frs
	 */
	public function __construct(FileResourceStream $frs) {
		$this->frs = $frs;
	}
	/**
	 * @param bool $removeLockFile unlink could collide with fopen command from another thread. Set online true 
	 * when necesseary. fopen will cause a permission denied exception in this case.
	 */
	public function release(bool $removeLockFile = false) {
		$this->frs->close();
		
		if (!$removeLockFile) return;
		
		try {
			IoUtils::unlink($this->frs->getFileName());
		} catch (IoException $e) { };
	}
	
// 	public function __destruct() {
// 		$this->release();
// 	}
}
