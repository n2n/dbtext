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

use rocket\impl\ei\component\prop\relation\RelationEiProp;
use n2n\util\ex\IllegalStateException;
use n2n\impl\persistence\orm\property\RelationEntityProperty;
use rocket\ei\manage\frame\EiFrame;
use rocket\ei\manage\EiObject;
use rocket\impl\ei\component\prop\relation\command\RelationEiCommand;
use rocket\ei\manage\entry\EiEntry;
use n2n\impl\persistence\orm\property\relation\MappedRelation;
use rocket\ei\manage\ManageState;
use rocket\ei\manage\frame\EiRelation;
use n2n\web\http\controller\ControllerContext;
use n2n\persistence\orm\CascadeType;
use rocket\ei\EiType;
use n2n\util\uri\Path;
use rocket\ei\component\EiFrameFactory;
use rocket\impl\ei\component\prop\relation\command\RelationAjahEiCommand;
use rocket\impl\ei\component\prop\relation\command\RelationJhtmlController;
use rocket\ei\EiCommandPath;
use rocket\ei\manage\security\InaccessibleEiCommandPathException;
use n2n\util\uri\Url;
use rocket\ei\EiPropPath;
use n2n\util\type\CastUtils;
use rocket\ei\component\InvalidEiComponentConfigurationException;
use rocket\impl\ei\component\prop\relation\model\RelationEntry;
use n2n\web\http\HttpContext;
use n2n\reflection\property\PropertiesAnalyzer;
use n2n\reflection\ReflectionException;
use rocket\ei\mask\EiMask;
use rocket\ei\util\Eiu;
use n2n\l10n\Lstr;

abstract class EiPropRelation {
	protected $targetEiType;
	protected $targetEiMask;
	protected $targetSubEiTypeExtensions;
	protected $targetMasterEiProp;
	protected $targetMasterAccessProxy;
	
	protected $relationEiProp;
	protected $sourceMany;
	protected $targetMany;
	
	protected $filtered = true;
	
	protected $relationEiCommand;
	protected $embeddedEditEiCommand;
	protected $relationAjahEiCommand;
	
	public function __construct(RelationEiProp $relationEiProp, bool $sourceMany, bool $targetMany) {
		$this->relationEiProp = $relationEiProp;
		$this->sourceMany = $sourceMany;
		$this->targetMany = $targetMany;
	}
	
	/**
	 * @return \rocket\impl\ei\component\prop\relation\RelationEiProp
	 */
	public function getRelationEiProp(): RelationEiProp {
		return $this->relationEiProp;
	}
	
	/**
	 * @return bool
	 */
	public function isSourceMany(): bool {
		return $this->sourceMany;
	}
	
	/**
	 * @return bool
	 */
	public function isTargetMany(): bool {
		return $this->targetMany;
	}
	
	/**
	 * @throws IllegalStateException
	 * @return \rocket\ei\EiType
	 */
	public function getTargetEiType(): EiType {
		if ($this->targetEiType === null) {
			throw new IllegalStateException(get_class($this->relationEiProp) . ' not set up');
		}
		return $this->targetEiType;
	}
	
	/**
	 * @throws IllegalStateException
	 * @return \rocket\ei\mask\EiMask
	 */
	public function getTargetEiMask(): EiMask {
		if ($this->targetEiMask === null) {
			throw new IllegalStateException(get_class($this->relationEiProp) . ' not set up');
		}
		return $this->targetEiMask;
	}
	
	
	/**
	 * @throws InvalidEiComponentConfigurationException
	 */
	private function initTargetMasterEiProp() {
		$entityProperty = $this->getRelationEntityProperty();
		if ($entityProperty->isMaster()) return;
		
		$mappedRelation = $entityProperty->getRelation();
		CastUtils::assertTrue($mappedRelation instanceof MappedRelation);
		
		$targetEntityProperty = $mappedRelation->getTargetEntityProperty();
		
		foreach ($this->getTargetEiMask()->getEiPropCollection() as $targetEiProp) {
			if ($targetEiProp instanceof RelationEiProp
					&& $targetEntityProperty->equals($targetEiProp->getEntityProperty())) {
						$this->targetMasterEiProp = $targetEiProp;
						$this->targetMasterAccessProxy = $targetEiProp->getObjectPropertyAccessProxy();
						return;
					}
		}
		
		// 		if (!$this->getTargetEiMask()->getEiEngine()->getEiCommandCollection()->hasGenericOverview()) {
		// 			return;
		// 		}
		
		$targetClass = $targetEntityProperty->getEntityModel()->getClass();
		$propertiesAnalyzer = new PropertiesAnalyzer($targetClass);
		try {
			$this->targetMasterAccessProxy = $propertiesAnalyzer->analyzeProperty($targetEntityProperty->getName());
		} catch (ReflectionException $e) {
			throw new InvalidEiComponentConfigurationException('No Target master property accessible: '
					. $targetEntityProperty, 0, $e);
		}
	}
	
	/**
	 * @param EiType $targetEiType
	 * @param EiMask $targetEiMask
	 */
	public function init(Eiu $eiu, EiType $targetEiType, EiMask $targetEiMask, array $targetEiTypeExtensions) {
		$this->targetEiType = $targetEiType;
		$this->targetEiMask = $targetEiMask;
		$this->targetSubEiTypeExtensions = $targetEiTypeExtensions;
		
		$this->initTargetMasterEiProp();
		
		// supreme EiEngine to make command available in EiFrames with super context EiTypes.
		$superemeEiuMask = $eiu->mask()->supremeMask();
		$this->relationEiCommand = new RelationEiCommand($this);
		$superemeEiuMask->addEiCommand($this->relationEiCommand);
		
		$this->relationAjahEiCommand = new RelationAjahEiCommand($this);
		$targetEiMask->getEiCommandCollection()->add($this->relationAjahEiCommand);
		
		if (!$this->getRelationEntityProperty()->isMaster()) {
			$entityProperty = $this->getRelationEntityProperty();
			
			$eiu->mask()->addEiModificator(new TargetMasterRelationEiModificator($this));
		}
	}
	
	/**
	 * @return bool
	 */
	public function isFiltered(): bool {
		return $this->filtered;
	}
	
	/**
	 * @param bool $filtered
	 */
	public function setFiltered(bool $filtered) {
		$this->filtered = $filtered;
	}
	
	protected function setupEmbeddedEditEiCommand() {
		$this->embeddedEditEiCommand = new EmbeddedEditPseudoCommand(Lstr::create('Edit embedded in '
				. $this->getRelationEiProp()->getWrapper()->getEiPropCollection()->getEiMask()->getLabelLstr()
				. ' - ' . $this->getTargetEiMask()->getLabelLstr()),
				$this->getRelationEiProp()->getWrapper()->getEiPropPath(), $this->getTargetEiType()->getId());
		
		$this->targetEiMask->getEiCommandCollection()->add($this->embeddedEditEiCommand);
	}
	
	// 	public function hasRecursiveConflict(EiFrame $eiFrame) {
	// 		$target = $this->getTarget();
	// 		while (null !== ($eiFrame = $eiFrame->getParent())) {
	// 			if ($eiFrame->getContextEiEngine()->getEiMask()->getEiType()->equals($target)) {
	// 				return true;
	// 			}
	// 		}
	// 		return false;
	// 	}
	
	public function isReadOnly(EiEntry $mapping, EiFrame $eiFrame) {
		return $this->relationEiProp->getEditConfig()->isReadOnly()
		|| (!$mapping->isNew() && $this->relationEiProp->getEditConfig()->isConstant())
		|| (!$this->relationEiProp->isDraftable() && $mapping->getEiObject()->isDraft())
		|| ($this->isFiltered() && $eiFrame->getEiRelation(EiPropPath::from($this->relationEiProp)));
	}
	
	/**
	 * @return \n2n\impl\persistence\orm\property\RelationEntityProperty
	 */
	public function getRelationEntityProperty(): RelationEntityProperty {
		return $this->relationEiProp->getEntityProperty();
	}
	
	/**
	 * @throws IllegalStateException
	 * @return \n2n\reflection\property\AccessProxy
	 */
	public function getTargetMasterAccessProxy() {
		if ($this->targetMasterAccessProxy !== null) {
			return $this->targetMasterAccessProxy;
		}
		
		throw new IllegalStateException('No target master AccessProxy initialized. '
				. get_class($this->relationEiProp) . ' is probably not set up.');
	}
	
	/**
	 * @return \rocket\impl\ei\component\prop\relation\RelationEiProp|
	 */
	public function findTargetEiProp() {
		if ($this->targetMasterEiProp !== null) {
			return $this->targetMasterEiProp;
		}
		
		$targetEiMask = $this->getTargetEiMask();
		$relationEntityProperty = $this->getRelationEntityProperty();
		foreach ($targetEiMask->getEiPropCollection() as $targetEiProp) {
			if (!($targetEiProp instanceof RelationEiProp)) continue;
			
			$targetRelationEntityProperty = $targetEiProp->getEntityProperty();
			
			$targetRelation = $targetRelationEntityProperty->getRelation();
			if ($targetRelation instanceof MappedRelation
					&& $targetRelation->getTargetEntityProperty()->equals($relationEntityProperty)) {
				return $targetEiProp;
			}
		}
		
		return null;
	}
	
	// 	public function isMaster() {
	// 		return $this->getRelationEntityProperty()->isMaster();
	// 	}
	
	public function createTargetEiFrame(ManageState $manageState, EiFrame $eiFrame, EiObject $eiObject = null,
			ControllerContext $targetControllerContext): EiFrame {
				$targetEiFrame = $manageState->createEiFrame($this->getTargetEiMask()->getEiEngine(), $targetControllerContext, new EiCommandPath([]));
				$targetEiFrame->setSubEiTypeExtensions($this->targetSubEiTypeExtensions);
				$this->configureTargetEiFrame($targetEiFrame, $eiFrame, $eiObject);
				
				return $targetEiFrame;
	}
	
	public function createTargetReadPseudoEiFrame(EiFrame $eiFrame, EiEntry $eiEntry = null): EiFrame {
		$targetEiFrame = $this->createTargetPseudoEiFrame($eiFrame, $eiEntry, new EiCommandPath(array()));
		
		return $targetEiFrame;
	}
	
	public function createTargetEditPseudoEiFrame(EiFrame $eiFrame, EiEntry $eiEntry): EiFrame {
		$targetEiFrame = $this->createTargetPseudoEiFrame($eiFrame, $eiEntry, EiCommandPath::from($this->embeddedEditEiCommand));
		
		return $targetEiFrame;
	}
	
	private function createTargetPseudoEiFrame(EiFrame $eiFrame, EiEntry $eiEntry = null, ?EiCommandPath $eiCommandPath): EiFrame {
		$eiObject = null;
		if ($eiEntry !== null) {
			$eiObject = $eiEntry->getEiObject();
		}
		
		$targetCmdContextPath = $eiFrame->getControllerContext()->getCmdContextPath();
		$eiCommandPathStr = $this->relationEiCommand->getWrapper()->getEiCommandPath();
		if ($eiObject === null) {
			$targetCmdContextPath = $targetCmdContextPath->ext($eiCommandPathStr, 'rel');
		} else if ($eiObject->isNew()) {
			$targetCmdContextPath = $targetCmdContextPath->ext($eiCommandPathStr, 'relnewentry',
					$eiObject->getEiEntityObj()->getEiType()->getId());
		} else {
			$targetCmdContextPath = $targetCmdContextPath->ext($eiCommandPathStr, 'relentry',
					$eiEntry->getPid());
		}
		
		$targetControllerContext = new ControllerContext(new Path(array()), $targetCmdContextPath);
		$targetEiFrameFactory = new EiFrameFactory($this->getTargetEiMask()->getEiEngine());
		$targetEiFrame = $targetEiFrameFactory->create($targetControllerContext, $eiFrame->getManageState(), $eiFrame,
				$eiCommandPath);
		
		$targetEiFrame->setSubEiTypeExtensions($this->targetSubEiTypeExtensions);
		
		$this->configureTargetEiFrame($targetEiFrame, $eiFrame, $eiObject/*, $editCommandRequired*/);
		
		return $targetEiFrame;
	}
	
	public function applyEiExecution(EiFrame $targetEiFrame, bool $useEmbeddedEditEiCommand) {
		try {
			if ($useEmbeddedEditEiCommand) {
				
			} else {
				
			}
			return true;
		} catch (InaccessibleEiCommandPathException $e) {
			return false;
		}
	}
	
	protected function configureTargetEiFrame(EiFrame $targetEiFrame, EiFrame $eiFrame,
			EiObject $eiObject = null/*, $editCommandRequired = null*/) {
				if ($eiObject === null) return $targetEiFrame;
				
				if (null !== ($targetCriteriaFactory = $this->createTargetCriteriaFactory($eiObject))) {
					$targetEiFrame->getBoundry()->setCriteriaFactory($targetCriteriaFactory);
				}
				
				$this->applyTargetModificators($targetEiFrame, $eiFrame, $eiObject);
				
				return $targetEiFrame;
	}
	
	protected function createTargetCriteriaFactory(EiObject $eiObject) {
		if ($eiObject->isNew()) return null;
		
		if (!$this->getRelationEntityProperty()->isMaster() && !$this->isSourceMany()) {
			return new MappedOneToCriteriaFactory($this->getRelationEntityProperty()->getRelation(),
					$eiObject->getLiveObject());
		}
		
		return new RelationCriteriaFactory($this->getRelationEntityProperty(), $eiObject->getLiveObject());
	}
	
	protected function applyTargetModificators(EiFrame $targetEiFrame, EiFrame $eiFrame,
			EiObject $eiObject) {
		$targetEiProp = $this->findTargetEiProp();
		
		if (null !== $targetEiProp) {
			$targetEiModificatorCollection = $targetEiProp->getWrapper()->getEiPropCollection()->getEiMask()->getEiModificatorCollection();
			
			$targetEiFrame->setEiRelation($targetEiProp->getWrapper()->getEiPropPath(), new EiRelation($eiFrame, $eiObject,
					$this->relationEiProp));
			
			if (!$eiObject->isDraft()) {
				$targetEiFrame->registerListener(new MappedRelationEiModificator($targetEiFrame,
						RelationEntry::from($eiObject), EiPropPath::from($targetEiProp), $this->isSourceMany()));
			}
		} else if ($this->targetMasterAccessProxy !== null) {
			$targetEiFrame->registerListener(
					new PlainMappedRelationEiModificator($targetEiFrame, $eiObject->getLiveObject(),
							$this->targetMasterAccessProxy, $this->isSourceMany()));
		}
		
		if ($this->getRelationEntityProperty()->isMaster() && !$eiObject->isDraft()) {
			$targetEiFrame->registerListener(new MasterRelationEiModificator($targetEiFrame, $eiObject->getLiveObject(),
					$this->relationEiProp->getObjectPropertyAccessProxy(), $this->isTargetMany()));
		}
	}
	
	public function isPersistCascaded() {
		return $this->getRelationEntityProperty()->getRelation()->getCascadeType() & CascadeType::PERSIST;
	}
	
	// 	public function isRemoveCascaded() {
	// 		return $this->getRelationEntityProperty()->getRelation()->getCascadeType() & CascadeType::REMOVE;
	// 	}
	
	// 	public function isJoinTableRelation() {
	// 		return $this->getRelationEntityProperty()->getRelation() instanceof JoinTableRelation;
	// 	}
	
	public function buildTargetNewEiuEntryFormUrl(EiEntry $eiEntry, bool $draft, EiFrame $eiFrame, HttpContext $httpContext): Url {
		$pathParts = array($this->relationEiCommand->getWrapper()->getEiCommandPath());
		if ($eiEntry->isNew()) {
			$pathParts[] = 'relnewentry';
			$pathParts[] = $eiEntry->getEiType()->getId();
		} else {
			$pathParts[] = 'relentry';
			$pathParts[] = $eiEntry->getPid();
		}
		$pathParts[] = $this->relationAjahEiCommand->getWrapper()->getEiCommandPath();
		$contextUrl = $httpContext->getControllerContextPath($eiFrame->getControllerContext())->ext(...$pathParts)
		->toUrl();
		return RelationJhtmlController::buildNewFormUrl($contextUrl, $draft);
	}
}
