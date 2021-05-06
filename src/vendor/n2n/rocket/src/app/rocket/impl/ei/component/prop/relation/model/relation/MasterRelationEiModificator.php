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

use rocket\ei\manage\frame\EiFrame;
use n2n\reflection\property\AccessProxy;
use rocket\ei\manage\entry\WrittenMappingListener;
use rocket\ei\manage\entry\EiEntry;
use rocket\ei\manage\frame\EiFrameListener;
use rocket\ei\manage\security\EiExecution;

class MasterRelationEiModificator implements EiFrameListener {
	private $targetEiFrame;
	private $entityObj;
	private $propertyAccessProxy;
	private $targetMany;

	public function __construct(EiFrame $targetEiFrame, $entityObj, AccessProxy $propertyAccessProxy, $targetMany) {
		$this->targetEiFrame = $targetEiFrame;
		$this->entityObj = $entityObj;
		$this->propertyAccessProxy = $propertyAccessProxy;
		$this->targetMany = (boolean) $targetMany;
	}

	public function onNewEiEntry(EiEntry $eiEntry) {
// 		if ($this->targetEiFrame !== $eiu->frame()->getEiFrame()) return;
		
		if ($eiEntry->getEiObject()->isDraft()) return;
		
		$that = $this;
		if (!$this->targetMany) {
			$eiEntry->registerListener(new WrittenMappingListener(
					function () use ($that, $eiEntry) {
						$that->propertyAccessProxy->setValue($that->entityObj, $eiEntry->getEiObject()->getLiveObject());
					}));
			return;
		}

		
		$eiEntry->registerListener(new WrittenMappingListener(
				function () use ($that, $eiEntry) {
					$targetEntities = $that->propertyAccessProxy->getValue($that->entityObj);
					if ($targetEntities === null) {
						$targetEntities = new \ArrayObject();
					}
					$targetEntities[] = $eiEntry->getEiObject()->getLiveObject();
					$that->propertyAccessProxy->setValue($that->entityObj, $targetEntities);
				}));
	}
	
	public function whenExecuted(EiExecution $eiExecution) {
	}

}
