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

use n2n\util\StringUtils;
use n2n\util\UnserializationFailedException;
use n2n\util\type\ArgUtils;
use n2n\io\managed\File;
use n2n\io\managed\FileSource;
use n2n\io\fs\FsPath;
use n2n\io\managed\FileListener;
use n2n\io\managed\InaccessibleFileSourceException;
use n2n\util\uri\Url;
use n2n\util\uri\UnavailableUrlException;

class CommonFile implements \Serializable, File {
	private $fileSource;
	private $originalName;
	private $originalExtension;
	private $fileListeners = array();
	
	/**
	 * @param string $path
	 * @param string $originalName
	 * @throws \InvalidArgumentException if $originalName is empty
	 */
	public function __construct(FileSource $fileSource, string $originalName) {
		$this->fileSource = $fileSource;
		$this->setOriginalName($originalName);
	}
	
	public function isValid(): bool {
		return $this->fileSource->isValid();
	}
	
	/**
	 * @return string
	 */
	public function getOriginalName(): string {
		return $this->originalName;
	}
	
	/**
	 * @param string $originalName
	 */
	public function setOriginalName(string $originalName) {
		ArgUtils::assertTrue(!StringUtils::isEmpty($originalName), '$originalName can not be empty.');
		
		$this->originalName = $originalName;
		if ($originalName === null) {
			$this->originalExtension = null;
		}
		$info = pathinfo($originalName);
		$this->originalExtension = isset($info['extension']) ? $info['extension'] : null;
	}
	
	/**
	 * @return string
	 */
	public function getOriginalExtension(): ?string {
		return $this->originalExtension;
	}
	
	/* (non-PHPdoc)
	 * @see \n2n\io\managed\File::setFileSource()
	 */
	public function setFileSource(FileSource $fileSource) {
		$this->fileSource = $fileSource;
	}
	
	/* (non-PHPdoc)
	 * @see \n2n\io\managed\File::getFileSource()
	 */
	public function getFileSource(): FileSource {
		return $this->fileSource;
	}
	
	/**
	 *
	 * @param FileListener $fileListener
	 */
	public function registerFileListener(FileListener $fileListener) {
		$this->fileListeners[spl_object_hash($fileListener)] = $fileListener;
	}
	/**
	 *
	 * @param string $fileListener
	 */
	public function unregisterFileListener(FileListener $fileListener) {
		unset($this->fileListeners[spl_object_hash($fileListener)]);
	}
	
	public function serialize() {
		foreach ($this->fileListeners as $fileListener) {
			$fileListener->onSerialize($this);
		}
		
		return serialize(array('fileSource' => $this->fileSource, 'originalName' => $this->originalName, 
				'originalExtension' => $this->originalExtension));
	}
	
	public function unserialize($serialized) {
		$data = StringUtils::unserialize($serialized);
		
		UnserializationFailedException::assertTrue(isset($data['fileSource']) && $data['fileSource'] instanceof FileSource
				&& array_key_exists('originalName', $data) 
				&& ($data['originalName'] === null || is_scalar($data['originalName']))
				&& array_key_exists('originalExtension', $data) 
				&& ($data['originalExtension'] === null || is_scalar($data['originalExtension'])));
		
		$this->fileSource = $data['fileSource'];
		$this->originalName = $data['originalName'];
		$this->originalExtension = $data['originalExtension'];
		
		foreach ($this->fileListeners as $fileListener) {
			$fileListener->unserialized($this);
		}
	}
	/**
	 * 
	 * @return string
	 */
	public function __toString(): string {
		return $this->getOriginalName() . ' (' . $this->fileSource->__toString() . ')';
	}
	
	
	/* (non-PHPdoc)
	 * @see \n2n\io\managed\File::move()
	 */
	public function move($fsPath, string $filePerm, bool $overwrite = true) {
		$fsPath = FsPath::create($fsPath);
		
		foreach ($this->fileListeners as $fileListener) {
			$fileListener->onMove($this, $fsPath, $overwrite);
		}
		
		$this->fileSource->move($fsPath, $filePerm, $overwrite);
		
		if ($this->fileSource->isValid()) {
			$this->fileSource = FileFactory::createFromFs($fsPath, $this->originalName);
		}
	}
	
	/* (non-PHPdoc)
	 * @see \n2n\io\managed\File::copy()
	 */
	public function copy($fsPath, string $filePerm, bool $overwrite = true): File {
		$fsPath = FsPath::create($fsPath);
		
		foreach ($this->fileListeners as $fileListener) {
			$fileListener->onCopy($this, $fsPath, $filePerm, $overwrite);
		}
		
		$this->fileSource->copy($fsPath, $filePerm, $overwrite);
		
		return new CommonFile(new FsFileSource($fsPath), $this->originalName);
	}
	
	/* (non-PHPdoc)
	 * @see \n2n\io\managed\File::delete()
	 */
	public function delete() {
		$this->triggerOnDelete();
		
		$this->fileSource->delete();
	}
	/**
	 * 
	 */
	public function __destruct() {
		foreach ($this->fileListeners as $fileListener) {
			$fileListener->onDestruct($this);
		}
		
// 		$this->fileSource->dispose();
	}
	/**
	 * 
	 * @param mixed $o
	 * @return boolean
	 */
	public function equals($o): bool {
		return $o instanceof File && $this->fileSource->equals($o->getFileSource());
	}
	
	public function toUrl(string &$suggestedLabel = null): Url {
		$suggestedLabel = $this->getOriginalName();
		try {
			return $this->getFileSource()->getUrl();
		} catch (InaccessibleFileSourceException $e) {
			throw new UnavailableUrlException(false, 0, $e);		
		}
	}
	
// 	public static function createFromAssignation(ManagerAssignation $fileAssignation, $originalName = null) {
// 		$file = new File($fileAssignation->getFilePath(), $originalName);
// 		$file->setFileAssignation($fileAssignation);
// 		return $file;
// 	}
}
