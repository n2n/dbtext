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
namespace n2n\persistence\orm\annotation;

use n2n\reflection\annotation\PropertyAnnotation;
use n2n\reflection\annotation\PropertyAnnotationTrait;
use n2n\reflection\annotation\AnnotationTrait;
use n2n\io\managed\FileLocator;
use n2n\io\managed\FileManager;

class AnnoManagedFile implements PropertyAnnotation {
	use PropertyAnnotationTrait, AnnotationTrait;
	
	private $fileManagerlookupId;
	private $fileLocator;
	private $cascadeDelete = true;
	/**
	 * @param string $fileManagerlookupId
	 * @param FileLocator $fileLocator
	 * @throws \InvalidArgumentException
	 */
	public function __construct(string $fileManagerlookupId = FileManager::TYPE_PUBLIC, FileLocator $fileLocator = null, bool $cascadeDelete = true) {
		$this->fileManagerlookupId = $fileManagerlookupId;
		$this->fileLocator = $fileLocator;
		$this->cascadeDelete = $cascadeDelete;
		
// 		$class = null;
// 		try {
// 			$class = ReflectionUtils::createReflectionClass($lookupId);
// 		} catch (TypeNotFoundException $e) {
// 			throw new \InvalidArgumentException('FileManager not found: ' . $lookupId, 0, $e);	
// 		}
		
// 		if (!$class->implementsInterface('n2n\io\managed\FileManager')) {
// 			throw new \InvalidArgumentException('Annotated FileManager class does not implement n2n\io\managed\FileManager: ' 
// 					. $lookupId);
// 		}
	}
	/**
	 * @return string 
	 */
	public function getLookupId() {
		return $this->fileManagerlookupId;
	}
	/**
	 * @return FileLocator
	 */
	public function getFileLocator() {
		return $this->fileLocator;
	}
	
	public function isCascadeDelete() {
		return $this->cascadeDelete;
	}
}
