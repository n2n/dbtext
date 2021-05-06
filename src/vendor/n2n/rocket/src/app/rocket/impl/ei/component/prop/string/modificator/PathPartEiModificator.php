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
namespace rocket\impl\ei\component\prop\string\modificator;

use rocket\impl\ei\component\modificator\adapter\EiModificatorAdapter;
use rocket\ei\manage\frame\EiFrame;
use rocket\ei\manage\entry\EiEntry;
use n2n\util\col\ArrayUtils;
use n2n\io\IoUtils;
use n2n\persistence\orm\criteria\item\CrIt;
use n2n\persistence\orm\property\BasicEntityProperty;
use rocket\ei\util\Eiu;
use rocket\ei\EiPropPath;
use rocket\impl\ei\component\prop\string\conf\PathPartConfig;
use rocket\impl\ei\component\prop\string\PathPartEiProp;
use rocket\ei\util\spec\EiuMask;

class PathPartEiModificator extends EiModificatorAdapter {
	private $pathPartConfig;
	private $eiPropPath;
	private $eiuMask;
	
	/**
	 * @param PathPartConfig $pathPartConfig
	 * @param EiPropPath $eiPropPath
	 */
	public function __construct(PathPartConfig $pathPartConfig, EiPropPath $eiPropPath, EiuMask $eiuMask) {
		$this->pathPartConfig = $pathPartConfig;
		$this->eiPropPath = $eiPropPath;
		$this->eiuMask = $eiuMask;
	}
	
	public function setupEiEntry(Eiu $eiu) {
		$pathPartPurify = new PathPartPurifier($eiu->frame()->getEiFrame(), $eiu->entry()->getEiEntry(), 
				$this->pathPartConfig, $this->eiPropPath, $this->eiuMask);
		$eiu->entry()->onWrite(function () use ($pathPartPurify) {
			$pathPartPurify->purify();
		});
	}	
}

class PathPartPurifier {
	private $eiFrame;
	private $eiEntry;
	private $pathPartConfig;
	private $eiPropPath;
	private $eiuMask;
	
	public function __construct(EiFrame $eiFrame, EiEntry $eiEntry, PathPartConfig $pathPartConfig, 
			EiPropPath $eiPropPath, EiuMask $eiuMask) {
		$this->eiFrame = $eiFrame;
		$this->eiEntry = $eiEntry;
		$this->pathPartConfig = $pathPartConfig;
		$this->eiPropPath = $eiPropPath;
		$this->eiuMask = $eiuMask;
	}
	
	private function getIdEntityProperty(): BasicEntityProperty {
		return $this->eiuMask->type()->getEntityModel()->getIdDef()->getEntityProperty();
	}
	
	public function purify() {
		$value = $this->eiEntry->getValue($this->eiPropPath);
	
		if ($value !== null) {
			$this->eiEntry->setValue($this->eiPropPath, $this->uniquePathPart(IoUtils::stripSpecialChars($value)));
			return;
		}
	
		if ($this->eiEntry->isNew() || !$this->pathPartConfig->isNullAllowed()) {
			$this->eiEntry->setValue($this->eiPropPath, $this->uniquePathPart($this->generatePathPart()));
		} else {
			$this->eiEntry->setValue($this->eiPropPath, null);
		}
	}
	
	private function generatePathPart(): string {
		$baseScalarEiProperty = $this->pathPartConfig->getBaseScalarEiProperty();
		if ($baseScalarEiProperty === null) {
			$baseScalarEiProperty = ArrayUtils::first($this->eiuMask->engine()->getScalarEiProperties());
		}
	
		if ($baseScalarEiProperty !== null) {
			return mb_strtolower(IoUtils::stripSpecialChars($baseScalarEiProperty->eiFieldValueToScalarValue(
					$this->eiEntry->getValue($baseScalarEiProperty->getEiPropPath()))));
		}
	
		if (null !== ($id = $this->eiEntry->getId())) {
			return mb_strtolower(IoUtils::stripSpecialChars($this->getIdEntityProperty()->valueToRep($id)));
		}
	
		return IoUtils::stripSpecialChars(uniqid());
	}
	
	private function uniquePathPart(string $pathPart): string {
		$uniquePathPart = $pathPart;
		for ($i = 2; $this->containsPathPart($uniquePathPart); $i++) {
			$uniquePathPart = $pathPart . '-' . $i;
		}
		return $uniquePathPart;
	}
	
	private function containsPathPart(string $pathPart): bool {
		$entityClass = $this->eiEntry->getEiMask()->getEiType()->getEntityModel()->getClass();
		$criteria = $this->eiFrame->getManageState()->getEntityManager()->createCriteria();
		$criteria->select('COUNT(1)')
				->from($entityClass, 'e')
				->where()->match(CrIt::p('e', $this->pathPartConfig->getEntityProperty()), '=', $pathPart)
				->andMatch(CrIt::p('e', $this->getIdEntityProperty()), '!=', $this->eiEntry->getId());
		
		if (null !== ($uniquePerGenericEiProperty = $this->pathPartConfig->getUniquePerGenericEiProperty())) {
			$criteria->where()->match($uniquePerGenericEiProperty->createCriteriaItem(CrIt::p('e')),
					'=', $uniquePerGenericEiProperty->buildEntityValue($this->eiEntry));
		}
	
		return 0 < $criteria->toQuery()->fetchSingle();
	}
}
