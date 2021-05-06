<?php
/*
 * Copyright (c) 2012-2016, Hofmänner New Media.
 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
 *
 * This file is part of the n2n module ROCKET.
 *
 * ROCKET is free software: you can redistribute it and/or modify it under the terms of the
 * GNU Lesser General Public License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * ROCKET is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
 * the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details: http://www.gnu.org/licenses/
 *
 * The following people participated in this project:
 *
 * Andreas von Burg...........:	Architect, Lead Developer, Concept
 * Bert Hofmänner.............: Idea, Frontend UI, Design, Marketing, Concept
 * Thomas Günther.............: Developer, Frontend UI, Rocket Capability for Hangar
 */
namespace rocket\impl\ei\component\prop\relation\model\relation;

use rocket\impl\ei\component\modificator\adapter\EiModificatorAdapter;
use rocket\ei\manage\entry\EiEntry;
use rocket\ei\manage\entry\EiEntryListenerAdapter;
use rocket\ei\util\Eiu;
use rocket\impl\ei\component\prop\relation\conf\RelationModel;

class TargetMasterRelationEiModificator extends EiModificatorAdapter {
	private $relationModel;

	public function __construct(RelationModel $relationModel) {
		$this->relationModel = $relationModel;
	}

	public function setupEiEntry(Eiu $eiu) {
		$eiEntry = $eiu->entry()->getEiEntry();
		if ($eiEntry->getEiObject()->isDraft()) return;
		
		$eiEntry->registerListener(new TargetMasterEiEntryListener($this->relationModel));
	}
}

class TargetMasterEiEntryListener extends EiEntryListenerAdapter {
	private $relationModel;
	private $accessProxy;
	private $orphanRemoval;
	
	private $oldValue;
	
	public function __construct(RelationModel $relationModel) {
		$this->relationModel = $relationModel;
		$this->accessProxy = $this->relationModel->getObjectPropertyAccessProxy();
		$this->orphanRemoval = $this->relationModel->getRelationEntityProperty()->getRelation()->isOrphanRemoval();
	}
	
	public function onWrite(EiEntry $eiEntry) {
		$this->oldValue = $this->accessProxy->getValue($eiEntry->getEiObject()->getLiveObject());
	}
	
	public function written(EiEntry $eiEntry) {
		$entityObj = $eiEntry->getEiObject()->getLiveObject();
		
		if ($this->relationModel->isTargetMany()) {
			$this->writeToMany($entityObj);
		} else {
			$this->writeToOne($entityObj);
		}
	}
	
	private function writeToOne($entityObj) {
		$oldTargetEntityObj = $this->oldValue;
		$targetEntityObj = $this->accessProxy->getValue($entityObj);
		
		if (!$this->orphanRemoval && $oldTargetEntityObj !== null && $oldTargetEntityObj !== $targetEntityObj) {
			$this->removeFromMaster($entityObj, $oldTargetEntityObj);
		}
		
		if ($targetEntityObj !== null) {
			$this->writeToMaster($entityObj, $targetEntityObj);
		}
	}
	
	private function writeToMany($entityObj) {
		$targetEntityObjs = $this->accessProxy->getValue($entityObj);
		if ($targetEntityObjs === null) {
			$targetEntityObjs = array();
		}
		
		foreach ($targetEntityObjs as $targetEntityObj) {
			$this->writeToMaster($entityObj, $targetEntityObj);
		}
		
		if ($this->orphanRemoval) return;
		
		$obsoleteTargetEntityObjs = array();
		if ($this->oldValue !== null) {
			$obsoleteTargetEntityObjs = $this->oldValue->getArrayCopy();
		}
		
		foreach ($targetEntityObjs as $targetEntityObj) {
			foreach ($obsoleteTargetEntityObjs as $key => $oldTargetEntityObj) {
				if ($targetEntityObj === $oldTargetEntityObj) {
					unset($obsoleteTargetEntityObjs[$key]);
				}
			}
		}
	
		foreach ($obsoleteTargetEntityObjs as $obsoleteTargetEntityObj) {
			$this->removeFromMaster($entityObj, $obsoleteTargetEntityObj);
		}
	}
	
	/**
	 * @param object $entityObj
	 * @param object $targetEntityObj
	 */
	private function writeToMaster($entityObj, $targetEntityObj) {
		$targetAccessProxy = $this->relationModel->getTargetPropInfo()->masterAccessProxy;
	
		if (!$this->relationModel->isSourceMany()) {
			$targetAccessProxy->setValue($targetEntityObj, $entityObj);
			return;
		}
	
		$sourceEntityObjs = $targetAccessProxy->getValue($targetEntityObj);
		if ($sourceEntityObjs === null) {
			$sourceEntityObjs = new \ArrayObject();
		}
	
		foreach ($sourceEntityObjs as $sourceEntityObj) {
			if ($sourceEntityObj === $entityObj) return;
		}
	
		$sourceEntityObjs[] = $entityObj;
		$targetAccessProxy->setValue($targetEntityObj, $sourceEntityObjs);
	}
	
	private function removeFromMaster($entityObj, $targetEntityObj) {
		$targetAccessProxy = $this->relationModel->getTargetPropInfo()->masterAccessProxy;
	
		if (!$this->relationModel->isSourceMany()) {
			if ($entityObj === $targetAccessProxy->getValue($targetEntityObj)) {
				$targetAccessProxy->setValue($targetEntityObj, null);
			}
				
			return;
		}
	
		$sourceEntityObjs = $targetAccessProxy->getValue($targetEntityObj);
		if ($sourceEntityObjs === null) {
			$sourceEntityObjs = new \ArrayObject();
		}
	
		foreach ($sourceEntityObjs as $key => $sourceEntityObj) {
			if ($sourceEntityObj === $entityObj) {
				$sourceEntityObjs->offsetUnset($key);
				$targetAccessProxy->setValue($targetEntityObj, $sourceEntityObjs);
			}
		}
	}
	
}
