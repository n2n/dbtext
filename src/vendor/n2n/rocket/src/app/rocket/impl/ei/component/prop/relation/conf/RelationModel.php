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
namespace rocket\impl\ei\component\prop\relation\conf;

use n2n\impl\persistence\orm\property\RelationEntityProperty;
use rocket\ei\EiPropPath;
use n2n\util\type\ArgUtils;
use rocket\ei\util\spec\EiuEngine;
use rocket\ei\mask\EiMask;
use rocket\impl\ei\component\prop\relation\RelationEiProp;
use n2n\impl\persistence\orm\property\relation\MappedRelation;
use n2n\reflection\property\AccessProxy;
use n2n\reflection\ReflectionException;
use rocket\ei\component\InvalidEiComponentConfigurationException;
use n2n\reflection\property\PropertiesAnalyzer;
use n2n\util\type\CastUtils;
use n2n\persistence\orm\CascadeType;
use n2n\util\type\TypeUtils;
use n2n\util\ex\IllegalStateException;
use rocket\ei\EiCommandPath;
use rocket\impl\ei\component\prop\relation\model\relation\TargetMasterRelationEiModificator;
use rocket\ei\util\spec\EiuMask;
use rocket\impl\ei\component\prop\adapter\config\EditConfig;
use n2n\config\InvalidConfigurationException;

class RelationModel {
	const MODE_SELECT = 'select';
	const MODE_EMBEDDED = 'embedded';
	const MODE_INTEGRATED = 'integrated';
	
	const DEFAULT_MAX_PICKS_NUM = 20;

	/**
	 * @var RelationEiProp
	 */
	private $relationEiProp;
	/**
	 * @var bool
	 */
	private $sourceMany;
	/**
	 * @var bool
	 */
	private $targetMany;
	/**
	 * @var string
	 */
	private $mode;
	/**
	 * @var EditConfig
	 */
	private $editConfig;
	
	// Select
	
	private $filtered = true;
	private $hiddenIfTargetEmpty = false;
	private $maxPicksNum = self::DEFAULT_MAX_PICKS_NUM;
	
	// Embedded
	
	private $orphansAllowed = false;
	private $reduced = true;
	private $removable = true;
	
	// ToMany / ToOne
	
	private $min = 0;
	private $max = null;
	
	// EmbeddedToMany
	
	private $tragetOrderEiPropPath = null; 
	
	// Finalize
	
	/**
	 * @var EiuEngine
	 */
	private $targetEiuEngine;
	/**
	 * @var TargetPropInfo
	 */
	private $targetPropInfo;
	/**
	 * @var EiCommandPath
	 */
	private $targetReadEiCommandPath;
	/**
	 * @var EiCommandPath
	 */
	private $targetEditEiCommandPath;

	/**
	 * @param RelationEntityProperty $relationEntityProperty
	 * @param bool $sourceMany
	 * @param bool $targetMany
	 * @param bool $embedded
	 */
	function __construct(RelationEiProp $relationEiProp, bool $sourceMany, bool $targetMany, string $mode, 
			?EditConfig $editConfig) {
		$this->relationEiProp = $relationEiProp;
		$this->sourceMany = $sourceMany;
		$this->targetMany = $targetMany;

		ArgUtils::valEnum($mode, self::getModes());
		$this->mode = $mode;
		
		$this->editConfig = $editConfig;
	}
	
	/**
	 * @return \rocket\impl\ei\component\prop\adapter\config\EditConfig|null
	 */
	function getEditConfig() {
		return $this->editConfig;
	}
	
	/**
	 * @return boolean
	 */
	function isReadOnly() {
		IllegalStateException::assertTrue($this->editConfig !== null);
		
		return $this->editConfig->isReadOnly();
	}
	
	/**
	 * @return boolean
	 */
	function isMandatory() {
		IllegalStateException::assertTrue($this->editConfig !== null);
		
		return ($this->min > 0 || $this->editConfig->isMandatory());
	}
	
	/**
	 * @return string[]
	 */
	static function getModes() {
		return [self::MODE_SELECT, self::MODE_EMBEDDED, self::MODE_INTEGRATED];
	}
	
	/**
	 * @return \n2n\impl\persistence\orm\property\RelationEntityProperty
	 */
	function getRelationEntityProperty() {
		return $this->relationEiProp->getRelationEntityProperty();
	}
	
	function getObjectPropertyAccessProxy() {
		$accessProxy = $this->relationEiProp->getObjectPropertyAccessProxy();
		IllegalStateException::assertTrue($accessProxy !== null);
		return $accessProxy;
	}
	
	/**
	 * @return \n2n\l10n\Lstr
	 */
	function getLabelLstr() {
		return $this->relationEiProp->getLabelLstr();
	}
	
	/**
	 * @return boolean
	 */
	function isSourceMany() {
		return $this->sourceMany;
	}
	
	/**
	 * @return boolean
	 */
	function isTargetMany() {
		return $this->targetMany;
	}
	
	/**
	 * @return boolean
	 */
	function isMaster() {
		return $this->getRelationEntityProperty()->isMaster();
	}
	
	/**
	 * @return boolean
	 */
	function isSelect() {
		return $this->mode == self::MODE_SELECT;
	}
	
	/**
	 * @return boolean
	 */
	function isEmbedded() {
		return $this->mode == self::MODE_EMBEDDED;
	}
	
	/**
	 * @return boolean
	 */
	function isIntegrated() {
		return $this->mode == self::MODE_INTEGRATED;
	}
	
	/**
	 * @return boolean
	 */
	function isFiltered() {
		return $this->filtered;
	}
	
	/**
	 * @param boolean $filtered
	 */
	function setFiltered(bool $filtered) {
		$this->filtered = $filtered;
	}
	
	/**
	 * @return boolean
	 */
	function isHiddenIfTargetEmpty() {
		return $this->hiddenIfTargetEmpty;
	}
	
	/**
	 * @param boolean $hiddenIfTargetEmpty
	 */
	function setHiddenIfTargetEmpty($hiddenIfTargetEmpty) {
		$this->hiddenIfTargetEmpty = $hiddenIfTargetEmpty;
	}

	/**
	 * @return int
	 */
	function getMaxPicksNum() {
		return $this->maxPicksNum;
	}
	
	/**
	 * @param int|null $max
	 */
	function setMaxPicksNum(int $maxPicksNum) {
		$this->maxPicksNum = $maxPicksNum;
	}
	
	/**
	 * @return boolean
	 */
	function isOrphansAllowed() {
		return $this->orphansAllowed;
	}
	
	/**
	 * @param boolean $orphansAllowed
	 */
	function setOrphansAllowed($orphansAllowed) {
		$this->orphansAllowed = $orphansAllowed;
	}
	
	/**
	 * @return boolean
	 */
	function isReduced() {
		return $this->reduced;
	}
	
	/**
	 * @param boolean $reduced
	 */
	function setReduced($reduced) {
		$this->reduced = $reduced;
	}
	
	/**
	 * @return boolean
	 */
	function isRemovable() {
		return $this->removable;
	}
	
	/**
	 * @param boolean $removable
	 */
	function setRemovable($removable) {
		$this->removable = $removable;
	}
	
	/**
	 * @return int
	 */
	function getMin() {
		if ($this->min < 1 && $this->editConfig !== null && $this->editConfig->isMandatory()) {
			return 1;
		}
		
		return $this->min;
	}
	
	/**
	 * @param int $min
	 */
	function setMin(int $min) {
		$this->min = $min;
	}
	
	/**
	 * @return int|null
	 */
	function getMax() {
		if (!$this->isTargetMany()) {
			return 1;
		}
		
		return $this->max;
	}
	
	/**
	 * @param int|null $max
	 */
	function setMax(?int $max) {
		$this->max = $max;
	}
	
	/**
	 * @return EiPropPath|null
	 */
	function getTargetOrderEiPropPath() {
		return $this->tragetOrderEiPropPath;
	}
	
	/**
	 * @param EiPropPath|null $tragetOrderEiPropPath
	 */
	function setTargetOrderEiPropPath(?EiPropPath $tragetOrderEiPropPath) {
		$this->tragetOrderEiPropPath = $tragetOrderEiPropPath;
	}
	
	/**
	 * @param EiCommandPath $targetEiCommandPath
	 */
	function setTargetReadEiCommandPath(EiCommandPath $targetEiCommandPath) {
		$this->targetReadEiCommandPath = $targetEiCommandPath;
	}
	
	/**
	 * @throws IllegalStateException
	 * @return \rocket\ei\EiCommandPath
	 */
	function getTargetReadEiCommandPath() {
		if ($this->targetReadEiCommandPath !== null) {
			return $this->targetReadEiCommandPath;
		}
		
		throw new IllegalStateException('TargetReadEiCommandPath not defined.');
	}
	/**
	 * @param EiCommandPath $targetEiCommandPath
	 */
	function setTargetEditEiCommandPath(EiCommandPath $targetEiCommandPath) {
		$this->targetEditEiCommandPath = $targetEiCommandPath;
	}
	
	/**
	 * @throws IllegalStateException
	 * @return \rocket\ei\EiCommandPath
	 */
	function getTargetEditEiCommandPath() {
		if ($this->targetEditEiCommandPath !== null) {
			return $this->targetEditEiCommandPath;
		}
		
		throw new IllegalStateException('TargetEditEiCommandPath not defined.');
	}
	
	function prepare(EiuMask $eiuMask, EiuMask $targetEiuMask) {
		if (!$this->getRelationEntityProperty()->isMaster()) {
			$eiuMask->addEiModificator(new TargetMasterRelationEiModificator($this));
		}
		$targetEiuMask->onEngineReady(function ($eiuEngine) {
			try {
				$this->finalize($eiuEngine);
			} catch (InvalidConfigurationException $e) {
				throw new InvalidEiComponentConfigurationException('Failed to setup EiProp: ' . $this->relationEiProp,
						0, $e);
			}
		});
	}
	
	/**
	 * @param EiuEngine $targetEiuEngine
	 */
	private function finalize(EiuEngine $targetEiuEngine) {
		$rf = new RelationFinalizer($this);
		
		$this->targetPropInfo = $rf->deterTargetPropInfo($targetEiuEngine);
		if ($this->isEmbedded() || $this->isIntegrated()) {
			$rf->validateEmbeddedOrIntegrated($targetEiuEngine);
		} else {
			$rf->validateNonEmbeddedOrIntegrated();
		}
		$this->targetEiuEngine = $targetEiuEngine;
		
		
		
// 		$this->embeddedEditPseudoCommand = new EmbeddedEditPseudoCommand($this->getRelationEiProp()->getEiEngine()->getEiMask()->getEiType()->getEiMask()->getLabel()
// 		// 						. ' > ' . $this->relationEiProp->getLabel() . ' Embedded Edit',
// 		// 				$this->getRelationEiProp()->getId(), $this->getTarget()->getId());
				
// 		// 		$this->getTarget()->getEiEngine()->getEiCommandCollection()->add($this->embeddedEditPseudoCommand);
		
	}
	
	/**
	 * @return \rocket\impl\ei\component\prop\relation\conf\TargetPropInfo
	 */
	function getTargetPropInfo() {
		IllegalStateException::assertTrue($this->targetPropInfo !== null);
		return $this->targetPropInfo;
	}
	
	/**
	 * @return \rocket\ei\util\spec\EiuEngine
	 */
	function getTargetEiuEngine() {
		IllegalStateException::assertTrue($this->targetEiuEngine !== null);
		return $this->targetEiuEngine;
	}
}


class RelationFinalizer {
	private $relationModel;
	
	/**
	 * @param RelationModel $relationModel
	 */
	function __construct(RelationModel $relationModel) {
		$this->relationModel = $relationModel;
	}
	
	/**
	 * @throws InvalidEiComponentConfigurationException
	 */
	function deterTargetPropInfo(EiuEngine $targetEiuEngine) {
		$targetEiMask = $targetEiuEngine->getEiEngine()->getEiMask();
		
		if (!$this->relationModel->isMaster()) {
			return self::deterTargetMaster($targetEiMask);
		}
		
		return self::deterTargetMapped($targetEiMask);
	}
	
	/**
	 * @param RelationEntityProperty $entityProperty
	 * @param EiMask $targetEiMask
	 * @return TargetPropInfo
	 */
	private function deterTargetMapped(EiMask $targetEiMask) {
		$relationEntityProperty = $this->relationModel->getRelationEntityProperty();
		
		foreach ($targetEiMask->getEiPropCollection() as $targetEiProp) {
			if (!($targetEiProp instanceof RelationEiProp)) continue;
			
			$targetRelationEntityProperty = $targetEiProp->getRelationEntityProperty();
			
			$targetRelation = $targetRelationEntityProperty->getRelation();
			if ($targetRelation instanceof MappedRelation
					&& $targetRelation->getTargetEntityProperty()->equals($relationEntityProperty)) {
				return new TargetPropInfo(EiPropPath::from($targetEiProp), 
						$targetEiProp->getObjectPropertyAccessProxy());
			}
		}
		
		return new TargetPropInfo();
	}
	
	/**
	 * @param RelationEntityProperty $entityProperty
	 * @param EiMask $targetEiMask
	 * @throws InvalidEiComponentConfigurationException
	 * @return TargetPropInfo
	 */
	private function deterTargetMaster(EiMask $targetEiMask) {
		$mappedRelation = $this->relationModel->getRelationEntityProperty()->getRelation();
		CastUtils::assertTrue($mappedRelation instanceof MappedRelation);
		
		$targetEntityProperty = $mappedRelation->getTargetEntityProperty();
		
		foreach ($targetEiMask->getEiPropCollection() as $targetEiProp) {
			if (($targetEiProp instanceof RelationEiProp)
					&& $targetEntityProperty->equals($targetEiProp->getRelationEntityProperty())) {
				return new TargetPropInfo(EiPropPath::from($targetEiProp), $targetEiProp->getObjectPropertyAccessProxy());
			}
		}
		
		$targetClass = $targetEiMask->getEiType()->getEntityModel()->getClass();
		$propertiesAnalyzer = new PropertiesAnalyzer($targetClass);
		try {
			return new TargetPropInfo(null, $propertiesAnalyzer->analyzeProperty($targetEntityProperty->getName()));
		} catch (ReflectionException $e) {
			throw new InvalidEiComponentConfigurationException('No target master property accessible: '
					. $targetEntityProperty, 0, $e);
		}
	}
	
	function validateNonEmbeddedOrIntegrated() {
		$entityProperty = $this->relationModel->getRelationEntityProperty();
		
		if ($this->relationModel->isReadOnly() || $this->relationModel->isMaster() 
				|| $entityProperty->getRelation()->isOrphanRemoval() || $this->relationModel->isSourceMany()
				|| $this->relationModel->getTargetPropInfo()->masterAccessProxy->getConstraint()->allowsNull()) {
			return;			
		}
		
		throw new InvalidEiComponentConfigurationException('Non-master OneToXEiProp is editable and doesn\'t remove '
				. 'orphans. So target master property must allow null: '
				. $this->relationModel->getTargetPropInfo()->masterAccessProxy);
	}
		
	
	function validateEmbeddedOrIntegrated() {
		$entityProperty = $this->relationModel->getRelationEntityProperty();
		
		if (!($entityProperty->getRelation()->getCascadeType() & CascadeType::PERSIST)) {
			throw new InvalidEiComponentConfigurationException(
					'EiProp requires an EntityProperty which cascades persist: '
							. TypeUtils::prettyClassPropName($entityProperty->getEntityModel()->getClass(),
									$entityProperty->getName()));
		}
		
		// 		if ($this->isDraftable() && !$this->isJoinTableRelation($this)) {
		// 			throw new InvalidEiComponentConfigurationException(
		// 					'Only EiProps of properties with join table relations can be drafted.');
		// 		}
		
		// reason to remove: orphans should never remain in db on embeddedeiprops
		if ($entityProperty->getRelation()->isOrphanRemoval()) {
			return;
		}
		
		if (!$this->relationModel->isOrphansAllowed()) {
			throw new InvalidEiComponentConfigurationException('EiProp requires an EntityProperty '
					. TypeUtils::prettyClassPropName($entityProperty->getEntityModel()->getClass(), $entityProperty->getName())
					. ' which removes orphans or an EiProp configuration with '
					. RelationConfig::ATTR_ORPHANS_ALLOWED_KEY . '=true.');
		} 
		
		if (!$entityProperty->isMaster() && !$this->relationModel->isSourceMany()
				&& !$this->relationModel->getTargetPropInfo()->masterAccessProxy->getConstraint()->allowsNull()) {
			throw new InvalidEiComponentConfigurationException('EiProp requires an EntityProperty '
					. TypeUtils::prettyClassPropName($entityProperty->getEntityModel()->getClass(), $entityProperty->getName())
					. ' which removes orphans or target ' . $this->getTargetMasterAccessProxy()
					. ' must accept null.');
		}
	}
}

class TargetPropInfo {
	/**
	 * @var EiPropPath|null
	 */
	public $eiPropPath;
	/**
	 * @var AccessProxy|null
	 */
	public $masterAccessProxy;
	
	function __construct(EiPropPath $eiPropPath = null, AccessProxy $masterAccessProxy = null) {
		$this->eiPropPath = $eiPropPath;
		$this->masterAccessProxy = $masterAccessProxy;
	}
	
}