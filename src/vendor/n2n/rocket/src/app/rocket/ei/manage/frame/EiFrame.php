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
namespace rocket\ei\manage\frame;

use n2n\util\ex\IllegalStateException;
use rocket\ei\mask\EiMask;
use n2n\persistence\orm\criteria\item\CrIt;
use n2n\core\container\N2nContext;
use rocket\ei\manage\entry\EiEntry;
use rocket\ei\manage\security\EiExecution;
use n2n\web\http\HttpContext;
use n2n\util\uri\Url;
use n2n\util\type\ArgUtils;
use rocket\ei\EiCommandPath;
use rocket\ei\EiEngine;
use rocket\ei\manage\ManageState;
use rocket\ei\manage\EiObject;
use rocket\ei\manage\security\EiEntryAccess;
use rocket\ei\EiPropPath;
use rocket\ei\component\command\EiCommand;
use rocket\ei\manage\security\InaccessibleEiEntryException;
use rocket\ei\component\command\GenericResult;
use rocket\si\control\SiNavPoint;
use rocket\si\meta\SiFrame;
use rocket\ei\manage\api\ApiController;

class EiFrame {
	
	private $contextEiEngine;
	private $manageState;
	/**
	 * @var Boundry
	 */
	private $boundry;
	/**
	 * @var Ability
	 */
	private $ability;
	private $eiForkLink;
	private $baseUrl;
	
	private $eiExecution;
// 	private $eiObject;
// 	private $previewType;
	private $eiRelations = array();

// 	private $filterModel;
// 	private $sortModel;
	
// 	private $eiTypeConstraint;
	
// 	private $breadcrumbs = [];
	
	private $listeners = array();

	/**
	 * @param EiMask $contextEiEngine
	 * @param ManageState $manageState
	 */
	public function __construct(EiEngine $contextEiEngine, ManageState $manageState) {
		$this->contextEiEngine = $contextEiEngine;
		$this->manageState = $manageState;
		$this->boundry = new Boundry();
		$this->ability = new Ability();

// 		$this->eiTypeConstraint = $manageState->getSecurityManager()->getConstraintBy($contextEiMask);
	}

// 	/**
// 	 * @return \rocket\ei\EiType
// 	 */
// 	public function getContextEiType(): EiType {
// 		return $this->contextEiMask->getEiEngine()->getEiMask()->getEiType();
// 	}
	
	/**
	 * @return EiEngine
	 */
	public function getContextEiEngine() {	
		return $this->contextEiEngine;
	}
	
	/**
	 * @return ManageState
	 */
	public function getManageState() {
		return $this->manageState;
	}
	
	/**
// 	 * @throws \n2n\util\ex\IllegalStateException
// 	 * @return \n2n\persistence\orm\EntityManager
// 	 */
// 	public function getEntityManager(): EntityManager {
// 		return $this->manageState->getEntityManager();
// 	}
	
	/**
	 * @return N2nContext
	 */
	public function getN2nContext() {
		return $this->manageState->getN2nContext();
	}
	
	/**
	 * @param EiForkLink|null $forkLink
	 */
	public function setEiForkLink(?EiForkLink $forkLink) {
		$this->forkLink = $forkLink;
	}
	
	/**
	 * @return EiForkLink|null
	 */
	public function getEiForkLink() {
		return $this->forkLink;
	}
	
	/**
	 * @return boolean
	 */
	public function hasBaseUrl() {
		return $this->baseUrl !== null;
	}
	
	/**
	 * @param Url $url
	 */
	public function setBaseUrl(?Url $baseUrl) {
		$this->baseUrl = $baseUrl;
	}
	
	/**
	 * @return Url
	 */
	public function getBaseUrl() {
		if (null === $this->baseUrl) {
			throw new IllegalStateException('BaseUrl of EiFrame is unknown.');
		}
		
		return $this->baseUrl;
	}
	
	/**
	 * @param EiExecution $eiExecution
	 */
	public function exec(EiCommand $eiCommand) {
		if ($this->eiExecution !== null) {
			throw new IllegalStateException('EiFrame already executed.');
		}
		
		$this->eiExecution = $this->manageState->getEiPermissionManager()
				->createEiExecution($this->contextEiEngine->getEiMask(), $eiCommand);
		
		foreach ($this->listeners as $listener) {
			$listener->whenExecuted($this->eiExecution);
		}
	}
	
	/**
	 * @throws IllegalStateException
	 * @return EiExecution
	 */
	public function getEiExecution() {
		if (null === $this->eiExecution) {
			throw new IllegalStateException('EiFrame contains no EiExecution.');
		}
		
		return $this->eiExecution;
	}
	
	/**
	 * @return bool
	 */
	public function hasEiExecution() {
		return $this->eiExecution !== null;
	}
	
	public function setEiRelation(EiPropPath $eiPropPath, EiRelation $scriptRelation) {
		$this->eiRelations[(string) $eiPropPath] = $scriptRelation;
	}
	
	public function hasEiRelation(EiPropPath $eiPropPath) {
		return isset($this->eiRelations[(string) $eiPropPath]);
	}
	
	public function getEiRelation(EiPropPath $eiPropPath) {
		if (isset($this->eiRelations[(string) $eiPropPath])) {
			return $this->eiRelations[(string) $eiPropPath];
		}
		
		return null;
	}
	
	/**
	 * @return Boundry
	 */
	public function getBoundry() {
		return $this->boundry;
	}
	
	/**
	 * @return Ability
	 */
	public function getAbility() {
		return $this->ability;
	}
	
	/**
	 * @var \rocket\ei\manage\critmod\filter\FilterDefinition
	 */
	private $filterDefinition;
	/**
	 * @var \rocket\ei\manage\critmod\sort\SortDefinition
	 */
	private $sortDefinition;
	/**
	 * @var \rocket\ei\manage\critmod\quick\QuickSearchDefinition
	 */
	private $quickSearchDefinition;
	
	/**
	 * @return \rocket\ei\manage\critmod\filter\FilterDefinition
	 */
	public function getFilterDefinition() {
		if ($this->filterDefinition !== null) {
			return $this->filterDefinition;
		}
		
		return $this->filterDefinition = $this->contextEiEngine
				->createFramedFilterDefinition($this);
	}
	
	/**
	 * @return boolean
	 */
	public function hasFilterProps() {
		return !$this->getFilterDefinition()->isEmpty();
	}
	
	/**
	 * @return \rocket\ei\manage\critmod\sort\SortDefinition
	 */
	public function getSortDefinition() {
		if ($this->sortDefinition !== null) {
			return $this->sortDefinition;
		}
		
		return $this->sortDefinition = $this->contextEiEngine
				->createFramedSortDefinition($this);
	}
	
	/**
	 * @return boolean
	 */
	public function hasSortProps() {
		return !$this->getSortDefinition()->isEmpty();
	}
	
	/**
	 * @return \rocket\ei\manage\critmod\quick\QuickSearchDefinition
	 */
	public function getQuickSearchDefinition() {
		if ($this->quickSearchDefinition !== null) {
			return $this->quickSearchDefinition;
		}
		
		return $this->quickSearchDefinition = $this->contextEiEngine
				->createFramedQuickSearchDefinition($this);
	}
	
	/**
	 * @return boolean
	 */
	public function hasQuickSearchProps() {
		return !$this->getQuickSearchDefinition()->isEmpty();
	}
	

	/**
	 * @param \n2n\persistence\orm\EntityManager $em
	 * @param string $entityAlias
	 * @return \n2n\persistence\orm\criteria\Criteria
	 */
	public function createCriteria(string $entityAlias, int $ignoreConstraintTypes = 0) {
		$em = $this->manageState->getEntityManager();
		$criteria = null;
		$criteriaFactory = $this->boundry->getCriteriaFactory();		
		if ($criteriaFactory !== null && !($ignoreConstraintTypes & Boundry::TYPE_MANAGE)) {
			$criteria = $criteriaFactory->create($em, $entityAlias);
		} else {
			$criteria = $em->createCriteria()->from(
					$this->getContextEiEngine()->getEiMask()->getEiType()->getEntityModel()->getClass(), 
					$entityAlias);
		}

		$entityAliasCriteriaProperty = CrIt::p(array($entityAlias));
		
		if (!($ignoreConstraintTypes & Boundry::TYPE_SECURITY) 
				&& null !== ($criteriaConstraint = $this->getEiExecution()->getCriteriaConstraint())) {
			$criteriaConstraint->applyToCriteria($criteria, $entityAliasCriteriaProperty);
		}
		
		foreach ($this->boundry->filterCriteriaConstraints($ignoreConstraintTypes) as $criteriaConstraint) {
			$criteriaConstraint->applyToCriteria($criteria, $entityAliasCriteriaProperty);
		}

// 		if (!($ignoreConstraintTypes & Boundry::TYPE_SECURITY)
// 				&& null !== ($criteriaConstraint = $this->getEiExecution()->getCriteriaConstraint())) {
// 			$criteriaConstraint->applyToCriteria($criteria, $entityAliasCriteriaProperty);
// 		}
		
		return $criteria;
	}
	
	/**
	 * @param EiObject $eiObject
	 * @param int $ignoreConstraintTypes
	 * @return EiEntry
	 * @throws InaccessibleEiEntryException
	 */
	public function createEiEntry(EiObject $eiObject, EiEntry $copyFrom = null, int $ignoreConstraintTypes = 0) {
		$eiEntry = $this->contextEiEngine->getEiMask()->determineEiMask($eiObject->getEiEntityObj()->getEiType())->getEiEngine()
				->createFramedEiEntry($this, $eiObject, $copyFrom, $this->boundry->filterEiEntryConstraints($ignoreConstraintTypes));
		$eiEntry->setEiEntryAccess($this->getEiExecution()->createEiEntryAccess($eiEntry));
		
		foreach ($this->listeners as $listener) {
			$listener->onNewEiEntry($eiEntry);
		}
		
		return $eiEntry;
	}
	
	
	
// 	/**
// 	 * @throws IllegalStateException
// 	 * @return EiEntryAccessFactory
// 	 */
// 	public function getEiEntryAccessFactory() {
// 		return $this->getEiExecution()->getEiEntryAccessFactory()->createEiEntryAccess($eiEntry);
// 	}
	
	/**
	 * @return bool
	 */
	public function hasEiEntryAccessFactory() {
		return $this->eiExecution !== null;
	}
	
	/**
	 * @param EiCommandPath $eiCommandPath
	 * @return bool
	 */
	public function isExecutableBy(EiCommandPath $eiCommandPath): bool {
		return $this->getEiEntryAccessFactory()->isExecutableBy($eiCommandPath);
	}
	
	/**
	 * @param EiEntry $eiEntry
	 * @return EiEntryAccess
	 */
	public function createEiEntryAccess(EiEntry $eiEntry): EiEntryAccess {
		return $this->getEiEntryAccessFactory()->createEiEntryAccess($eiEntry);
	}
	
	/**
	 * @return boolean
	 */
	public function isOverviewAvailable() {
		return $this->getContextEiEngine()->getEiMask()->getEiCommandCollection()->hasGenericOverview();
	}
	
	/**
	 * @param bool $required
	 * @return SiNavPoint|null
	 */
	public function getOverviewNavPoint(bool $required = true) {
		$result = $this->getContextEiEngine()->getEiMask()->getEiCommandCollection()
				->determineGenericOverview($required);
				
		return $this->compleNavPoint($result);
	}
	
	/**
	 * @param EiObject $eiObject
	 * @return boolean
	 */
	public function isDetailAvailable(EiObject $eiObject) {
		return $this->getContextEiEngine()->getEiMask()->getEiCommandCollection()->hasGenericDetail($eiObject);
	}
	
	/**
	 * @param EiObject $eiObject
	 * @param bool $required
	 * @return SiNavPoint|null
	 */
	public function getDetailNavPoint(EiObject $eiObject, bool $required = true) {
		$result = $this->getContextEiEngine()->getEiMask()->getEiCommandCollection()
				->determineGenericDetail($eiObject, $required);
		
		return $this->compleNavPoint($result);
	}
	
	/**
	 * @param EiObject $eiObject
	 * @return boolean
	 */
	public function isEditAvailable(EiObject $eiObject) {
		return $this->getContextEiEngine()->getEiMask()->getEiCommandCollection()->hasGenericEdit($eiObject);
	}
	
	/**
	 * @param EiObject $eiObject
	 * @param bool $required
	 * @return SiNavPoint|null
	 */
	public function getEditNavPoint(EiObject $eiObject, bool $required = true) {
		$result = $this->getContextEiEngine()->getEiMask()->getEiCommandCollection()
				->determineGenericEdit($eiObject, $required);
		
		return $this->compleNavPoint($result);
	}
	
	/**
	 * @param EiObject $eiObject
	 * @return boolean
	 */
	public function isAddAvailable(EiObject $eiObject) {
		return $this->getContextEiEngine()->getEiMask()->getEiCommandCollection()->hasGenericAdd();
	}
	
	/**
	 * @param bool $required
	 * @return SiNavPoint|null
	 */
	public function getAddNavPoint(bool $required = true) {
		$result = $this->getContextEiEngine()->getEiMask()->getEiCommandCollection()
				->determineGenericAdd($required);
				
		return $this->compleNavPoint($result);
	}
	
	/**
	 * @param GenericResult|null $result
	 * @return SiNavPoint|null
	 */
	private function compleNavPoint($result) {
		if ($result === null) {
			return null;
		}
		
		$navPoint = $result->getNavPoint();
		if ($navPoint->isUrlComplete()) {
			return $navPoint;
		}
		
		return $navPoint->complete($this->getBaseUrl()
				->ext(EiFrameController::createCmdUrlExt($result->getEiCommandPath())));
	}

	public function getApiUrl(?EiCommandPath $eiCommandPath, string $apiSection) {
		ArgUtils::valEnum($apiSection, ApiController::getApiSections());
		
		if ($eiCommandPath === null) {
			$eiCommandPath = EiCommandPath::from($this->getEiExecution()->getEiCommand());
		}
		
		return $this->getBaseUrl()->ext([EiFrameController::API_PATH_PART, (string) $eiCommandPath])->pathExt($apiSection);
	}
	
	public function getCmdUrl(EiCommandPath $eiCommandPath) {
		return $this->getBaseUrl()->ext([EiFrameController::CMD_PATH_PART, (string) $eiCommandPath]);
	}
	
	/**
	 * @param EiPropPath $eiPropPath
	 * @param string $mode
	 * @param EiEntry|null $eiEntry
	 * @return \n2n\util\uri\Url
	 */
	public function getForkUrl(?EiCommandPath $eiCommandPath, EiPropPath $eiPropPath, string $mode, EiObject $eiObject = null) {
		if ($eiCommandPath === null) {
			$eiCommandPath = EiCommandPath::from($this->getEiExecution()->getEiCommand());
		}
		
		if ($eiObject === null) {
			return $this->getBaseUrl()->ext([EiFrameController::FORK_PATH, (string) $eiCommandPath, (string) $eiPropPath, $mode]);
		}
		
		if ($eiObject->isNew()) {
			return $this->getBaseUrl()->ext([EiFrameController::FORK_NEW_ENTRY_PATH, (string) $eiCommandPath, 
					$eiObject->getEiEntityObj()->getEiType()->getId(), (string) $eiPropPath, $mode]);
		}
		
		return $this->getBaseUrl()->ext([EiFrameController::FORK_ENTRY_PATH, (string) $eiCommandPath, 
				$eiObject->getEiEntityObj()->getPid(), (string) $eiPropPath, $mode]);
	}
	
	
	
	
	private $currentUrlExt;
	
	public function setCurrentUrlExt(Url $currentUrlExt) {
		ArgUtils::assertTrue($currentUrlExt->isRelative(), 'Url must be relative.');
		$this->currentUrlExt = $currentUrlExt;
	}
	
	public function getCurrentUrlExt() {
		return $this->currentUrlExt;
	}
	
	public function getCurrentUrl(HttpContext $httpContext) {
		if ($this->currentUrlExt !== null) {
			return $httpContext->getRequest()->getContextPath()->toUrl()->ext($this->currentUrlExt);
		}
		
		return $httpContext->getRequest()->getRelativeUrl();
	}
	
	public function registerListener(EiFrameListener $listener) {
		$this->listeners[spl_object_hash($listener)] = $listener;
	}
	
	public function unregisterListener(EiFrameListener $listener) {
		unset($this->listeners[spl_object_hash($listener)]);		
	}
	
	/**
	 * @return \rocket\si\meta\SiFrame
	 */
	function createSiFrame() {
		$apUrlMap = array_combine(ApiController::getApiSections(), 
				array_map(fn ($section) => $this->getApiUrl(null, $section), ApiController::getApiSections()));
		return (new SiFrame($apUrlMap, $this->contextEiEngine->getEiMask()->getEiType()->createSiTypeContext()))
				->setSortable($this->ability->getSortAbility() !== null);
	}
}

class EiForkLink {
	/**
	 * View only
	 * @var string
	 */
	const MODE_DISCOVER = 'discover';
	/**
	 * E. g. OneToMany-, OneToOne- or ManyToManySelection
	 * @var string
	 */
	const MODE_SELECT = 'select';
	
	private $parent;
	private $mode;
	private $parentEiObject;
	
	function __construct(EiFrame $parent, string $mode, EiObject $parentEiObject = null) {
		$this->parent = $parent;
		ArgUtils::valEnum($mode, self::getModes());
		$this->mode = $mode;
		$this->parentEiObject = $parentEiObject;
		
		if ($parentEiObject !== null) {
			ArgUtils::assertTrue($parentEiObject->getEiEntityObj()->getEiType()
							->isA($parent->getContextEiEngine()->getEiMask()->getEiType()), 
					'EiForkLink EiObject is not compatible with EiFrame');	
		}
	}
	
	/**
	 * @return \rocket\ei\manage\frame\EiFrame
	 */
	function getParent() {
		return $this->parent;
	}
	
	/**
	 * @return string
	 */
	function getMode() {
		return $this->mode;
	}
	
	/**
	 * @return \rocket\ei\manage\EiObject|null
	 */
	function getParentEiObject() {
		return $this->parentEiObject;
	}
	
	/**
	 * @return string[]
	 */
	static function getModes() {
		return [self::MODE_DISCOVER, self::MODE_SELECT];
	}
}