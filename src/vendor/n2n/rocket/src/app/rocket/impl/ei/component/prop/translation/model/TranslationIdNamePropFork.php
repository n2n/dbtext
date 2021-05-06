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
namespace rocket\impl\ei\component\prop\translation\model;

use rocket\ei\manage\EiObject;
use rocket\ei\manage\idname\IdNameDefinition;
use rocket\ei\manage\idname\IdNamePropFork;
use rocket\ei\util\Eiu;
use rocket\impl\ei\component\prop\translation\TranslationEiProp;
use n2n\util\col\ArrayUtils;
use rocket\ei\manage\LiveEiObject;
use rocket\impl\ei\component\prop\relation\conf\RelationModel;
use n2n\reflection\ReflectionUtils;

class TranslationIdNamePropFork implements IdNamePropFork {
	/**
	 * @var RelationModel
	 */
	private $relationModel;
	/**
	 * @var TranslationEiProp
	 */
	private $eiProp;
	
	public function __construct(RelationModel $relationModel, TranslationEiProp $eiProp) {
		$this->relationModel = $relationModel;
		$this->eiProp = $eiProp;
	}
		
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\idname\IdNamePropFork::determineForkedEiObject()
	 */
	public function determineForkedEiObject(Eiu $eiu): ?EiObject {
		// @todo access locale and use EiObject with admin locale.
		
		$targetObjects = $eiu->object()->readNativValue($this->eiProp);

		if (empty($targetObjects)) {
			return null;
		}
		
		$targetObject = null;
		if ($targetObjects instanceof \ArrayObject) {
			$targetObject = ArrayUtils::first($targetObjects->getArrayCopy());
		}

		$targetEiuEngine = $this->relationModel->getTargetEiuEngine();
		
		$r = $targetEiuEngine->mask()->type()->newObject($targetObject)->getEiObject();
// 		if (ReflectionUtils::atuschBreak(100)) {
// 			throw new e();
// 		}
		return $r;
		
// 		return LiveEiObject::create($this->relationModel->getTargetEiuEngine()->getEiEngine()->getEiMask()->getEiType(), 
// 				);
	}

	public function getForkedIdNameDefinition(): IdNameDefinition {
		return $this->relationModel->getTargetEiuEngine()->getIdNameDefinition();
	}

	
}
