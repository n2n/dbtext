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

namespace rocket\ei\util\entry;

use n2n\l10n\N2nLocale;
use rocket\ei\EiPropPath;
use rocket\ei\manage\entry\OnWriteMappingListener;
use rocket\ei\manage\entry\WrittenMappingListener;
use rocket\ei\manage\entry\OnValidateMappingListener;
use rocket\ei\manage\entry\ValidatedMappingListener;
use rocket\ei\manage\entry\EiFieldOperationFailedException;
use rocket\ei\manage\gui\EiGuiSiFactory;
use rocket\ei\manage\DefPropPath;
use rocket\ei\manage\gui\GuiException;
use rocket\ei\manage\gui\ViewMode;
use rocket\ei\manage\entry\EiEntry;
use rocket\ei\util\EiuAnalyst;
use rocket\ei\util\spec\EiuMask;
use n2n\util\type\ArgUtils;
use rocket\ei\manage\entry\UnknownEiFieldExcpetion;
use rocket\ei\component\prop\EiProp;
use n2n\util\ex\NotYetImplementedException;
use rocket\ei\component\prop\EiPropWrapper;
use rocket\ei\util\gui\EiuGui;
use rocket\ei\manage\gui\EiGui;
use rocket\core\model\launch\TransactionApproveAttempt;
use n2n\persistence\orm\util\NestedSetUtils;
use n2n\persistence\orm\util\NestedSetStrategy;
use n2n\util\ex\IllegalStateException;

class EiuEntry {
	private $eiEntry;
	private $eiuAnalyst;
	private $eiuObject;
	private $eiuMask;
	
	/**
	 * @param EiEntry|null $eiEntry
	 * @param EiuObject|null $eiuObject
	 * @param EiuMask|null $eiuMask
	 * @param EiuAnalyst $eiuAnalyst
	 */
	public function __construct(EiEntry $eiEntry = null, EiuObject $eiuObject = null, EiuMask $eiuMask = null, EiuAnalyst $eiuAnalyst) {
		ArgUtils::assertTrue($eiEntry !== null || $eiuObject !== null);
		$this->eiEntry = $eiEntry;
		$this->eiuObject = $eiuObject;
		$this->eiuMask = $eiuMask;
		$this->eiuAnalyst = $eiuAnalyst;
	}
	
	/**
	 * @param bool $required
	 * @return \rocket\ei\util\frame\EiuFrame
	 */
	private function getEiuFrame(bool $required = true) {
		return $this->eiuAnalyst->getEiuFrame($required);
	}
	
	/**
	 * @return EiuMask
	 */
	public function mask() {
		if ($this->eiuMask !== null) {
			return $this->eiuMask;
		}
		
		if ($this->eiEntry !== null) {
			return $this->eiuMask = new EiuMask($this->eiEntry->getEiMask(), null, $this->eiuAnalyst);
		}
		
		
		if (null !== ($eiFrame = $this->eiuAnalyst->getEiFrame(false))) {
			return $this->eiuMask = new EiuMask(
					$eiFrame->getContextEiEngine()->getEiMask()->determineEiMask($this->eiuObject->getEiType()), 
					null, $this->eiuAnalyst);
		}
		
		return $this->eiuMask = new EiuMask($this->eiuObject->getEiType()->getEiMask(), null, $this->eiuAnalyst);
	}
	
	/**
	 * @return \rocket\ei\util\entry\EiuObject
	 */
	public function object() {
		if ($this->eiuObject !== null) {
			return $this->eiuObject;
		}
		
		return $this->eiuObject = new EiuObject($this->eiEntry->getEiObject(), $this->eiuAnalyst);
	}
	
	/**
	 * @return \rocket\ei\manage\EiObject
	 */
	private function getEiObject() {
		if ($this->eiuObject !== null) {
			return $this->eiuObject->getEiObject();
		}
		
		return $this->eiEntry->getEiObject();
	}
	
	/**
	 * @return boolean
	 */
	public function isInitialized() {
		return $this->eiEntry !== null;
	}
	
	/**
	 * @return \rocket\ei\manage\entry\EiEntry|NULL
	 */
	public function getEiEntry(bool $createdIfNotAvailable = true) {
		if ($this->eiEntry !== null) {
			return $this->eiEntry;
		}
		
		if (!$createdIfNotAvailable) {
			return null;
		}
				
		return $this->eiEntry = $this->eiuAnalyst->getEiFrame(false)
				->createEiEntry($this->getEiObject());
	}
	
	/**
	 * @return boolean
	 */
	public function isNew() {
		if ($this->isDraft()) {
			return $this->isDraftNew();
		} else {
			return !$this->isPersistent();
		}
	}
		
	/**
	 * @return \rocket\ei\manage\EiEntityObj
	 */
	public function getEiEntityObj() {
		return $this->getEiObject()->getEiEntityObj();
	}
	
	/**
	 * @return object
	 */
	public function getEntityObj() {
		return $this->getEiObject()->getEiEntityObj()->getEntityObj();
	}
	
	/**
	 * @return boolean
	 */
	public function isPersistent() {
		return $this->getEiObject()->getEiEntityObj()->isPersistent();
	}
	
	public function hasId() {
		return $this->getEiObject()->getEiEntityObj()->hasId();
	}
	
	/**
	 * @param bool $required
	 * @return mixed
	 */
	public function getId(bool $required = true) {
		$eiEntityObj = $this->getEiEntityObj();
		
		if (!$required && !$eiEntityObj->isPersistent()) {
			return null;
		}
		
		return $eiEntityObj->getId();
	}
	
	/*
	 * @param bool $required
	 * @return string
	 */
	public function getPid(bool $required = true) {
		if (null !== ($id = $this->getId($required))) {
			return $this->getEiType()->idToPid($id);
		}
		
		return null;
	}
	
	/**
	 * @return \rocket\ei\EiType
	 */
	public function getEiType() {
		return $this->getEiEntityObj()->getEiType();
	}
	
	/**
	 * @return boolean
	 */
	public function isDraft() {
		return $this->getEiObject()->isDraft();
	}
	
	/**
	 * @param bool $required
	 * @return \rocket\ei\manage\draft\Draft
	 */
	public function getDraft(bool $required = true) {
		if (!$required && !$this->isDraft()) {
			return null;
		}
		
		return $this->eiEntry->getEiObject()->getDraft();
	}
	
	/**
	 * @return boolean
	 */
	public function isDraftNew() {
		return $this->getDraft()->isNew();
	}
	
	/**
	 * @param bool $required
	 * @return mixed
	 */
	public function getDraftId(bool $required = true) {
		$draft = $this->getDraft();
		
		if (!$required && $draft->isNew()) {
			return null;
		}
		
		return $draft->getId();
	}
	
	/**
	 * @return boolean
	 */
	public function isPreviewSupported() {
		return $this->getEiuFrame()->isPreviewSupported($this);
	}
	
	/**
	 * @param string $previewType
	 * @return string[]
	 */
	public function getPreviewTypeOptions() {
		return $this->getEiuFrame()->getPreviewTypeOptions($this);
	}
	
	/**
	 * @return string|null
	 */
	public function getDefaultPreviewType() {
		return $this->getEiuFrame()->getDefaultPreviewType($this);
	}
	
	/**
	 * @var boolean
	 */
	private $accessible;
	
	/**
	 * @return boolean
	 */
	public function isAccessible() {
		if (null !== $this->accessible) return $this->accessible;
		
		if ($this->eiEntry !== null) {
			return $this->accessible = true;
		}
		
		// @todo check exception and make $this->accessible = false if thrown.
		$this->getEiEntry(true);
		$this->accessible = true;
	}
	
	
	/**
	 * @param int $viewMode
	 * @param array $defPropPaths
	 * @param bool $guiStructureDeclarationsRequired
	 * @return \rocket\ei\util\gui\EiuGui
	 */
	function newGui(bool $bulky = true, bool $readOnly = true, array $defPropPathsArg = null, 
			bool $guiStructureDeclarationsRequired = true, bool $determineEiMask = true) {
		$viewMode = ViewMode::determine($bulky, $readOnly, $this->isNew());
		$defPropPaths = DefPropPath::buildArray($defPropPathsArg);
		
		$eiEntry = $this->getEiEntry(true);
		$eiMask = null;
		if ($determineEiMask) {
			$eiMask = $eiEntry->getEiMask();
		} else {
			$eiMask = $this->eiuAnalyst->getEiFrame(true)->getContextEiEngine()->getEiMask();
		}
		
		$obtainer = $this->eiuAnalyst->getManageState()->getEiGuiModelCache();
		$eiGui = new EiGui($obtainer->obtainEiGuiModel($eiMask, $viewMode, $defPropPaths, 
				$guiStructureDeclarationsRequired));
		$eiGui->appendEiEntryGui($this->eiuAnalyst->getEiFrame(true), [$eiEntry]);
		
		return new EiuGui($eiGui, null, $this->eiuAnalyst);
	}
	
// 	/**
// 	 * @param bool $eiObjectObj
// 	 * @param bool $editable
// 	 * @throws EiuPerimeterException
// 	 * @return \rocket\ei\util\gui\EiuEntryGui
// 	 */
// 	public function newEntryGui(bool $bulky = true, bool $editable = false, int $treeLevel = null, 
// 			bool $determineEiMask = true) {
// 		$eiEntry = $this->getEiEntry(true);
// 		$eiEngine = null;
// 		if ($determineEiMask) {
// 			$eiEngine = $eiEntry->getEiMask()->getEiEngine();
// 		} else {
// 			$eiEngine = $this->getEiFrame()->getContextEiEngine();
// 		}
		
// 		$viewMode = $this->deterViewMode($bulky, $editable);
// 		$eiFrame = $this->getEiuFrame()->getEiFrame();
		
// 		$eiGuiFrame = $eiFrame->getManageState()->getDef()->getGuiDefinition($eiEngine->getEiMask())
// 				->createEiGuiFrame($eiFrame, $viewMode);
		
// 		return new EiuEntryGui($eiGuiFrame->createEiEntryGui($eiEntry, $treeLevel), null, $this->eiuAnalyst);
// 	}
	
	public function newCustomEntryGui(\Closure $uiFactory, array $defPropPaths, bool $bulky = true, 
			bool $editable = false, int $treeLevel = null, bool $determineEiMask = true) {
// 		$eiMask = null;
// 		if ($determineEiMask) {
// 			$eiMask = $this->determineEiMask();
// 		} else {
// 			$eiMask = $this->getEiFrame()->getContextEiEngine()->getEiMask();
// 		}
		
		$viewMode = $this->deterViewMode($bulky, $editable);
		$eiuGuiFrame = $this->getEiuFrame()->newCustomGui($viewMode, $uiFactory, $defPropPaths);
		return $eiuGuiFrame->appendNewEntryGui($this, $treeLevel);
	}
	
// 	/**
// 	 * @param int $viewMode
// 	 * @param bool $determineEiMask
// 	 * @return \rocket\ei\util\gui\EiuEntryGuiAssembler
// 	 */
// 	public function newEntryGuiAssembler(int $viewMode, bool $determineEiMask = true) {
// 		$eiFrame = $this->getEiuFrame()->getEiFrame();
// 		$eiMask = null;
// 		if ($determineEiMask) {
// 			$eiMask = $eiFrame->determineEiMask($this->eiEntry->getEiObject()->getEiEntityObj()->getEiType());
// 		} else {
// 			$eiMask = $eiFrame->getContextEiEngine()->getEiMask();
// 		}
		
// 		$eiGuiFrame = $eiMask->createEiGuiFrame($eiFrame, $viewMode, false);
// 		$eiGuiFrame->init(new DummyEiGuiSiFactory(), $eiGuiFrame->getGuiDefinition()->getDefPropPaths());
		
// 		$eiEntryGuiAssembler = new EiEntryGuiAssembler(new EiEntryGui($eiGuiFrame, $this->eiEntry));
		
// // 		if ($parentEiEntryGui->isInitialized()) {
// // 			throw new \InvalidArgumentException('Parent EiEntryGui already initialized.');
// // 		}
		
// // 		$parentEiEntryGui->registerEiEntryGuiListener(new InitListener($eiEntryGuiAssembler));
		
// 		return new EiuEntryGuiAssembler($eiEntryGuiAssembler, null, $this->eiuAnalyst);
// 	}
	
// 	/**
// 	 * @return \rocket\ei\mask\EiMask
// 	 */
// 	private function determineEiMask() {
// 		return $this->eiuFrame->getEiFrame()->determineEiMask($this->eiEntry->getEiObject()->getEiEntityObj()->getEiType());
// 	}
	
	/**
	 * @param bool $bulky
	 * @param bool $editable
	 * @return int
	 */
	public function deterViewMode(bool $bulky, bool $editable) {
		if (!$editable) {
			return $bulky ? ViewMode::BULKY_READ : ViewMode::COMPACT_READ;
		} else if ($this->isNew()) {
			return $bulky ? ViewMode::BULKY_ADD : ViewMode::COMPACT_ADD;
		} else {
			return $bulky ? ViewMode::BULKY_EDIT : ViewMode::COMPACT_EDIT;
		}
	}
	
	
	/**
	 * @param string|EiPropPath|EiPropWrapper|EiProp $eiPropArg
	 * @return boolean
	 */
	public function isFieldWritable($eiPropArg) {
		return $this->eiEntry->getEiEntryAccess()->isEiPropWritable(EiPropPath::create($eiPropArg));
	}
	
	/**
	 * @param mixed $eiPropArg
	 * @return \rocket\ei\util\entry\EiuField
	 */
	public function field($eiPropArg) {
		return new EiuField(EiPropPath::create($eiPropArg), $this, $this->eiuAnalyst);
	}
	
	public function getValue($eiPropPath) {
		return $this->getEiEntry()->getValue(EiPropPath::create($eiPropPath));
	}
	
	public function setValue($eiPropPath, $value) {
		return $this->getEiEntry()->setValue(EiPropPath::create($eiPropPath), $value);
	}
	
	public function getValues() {
		$eiEntry = $this->getEiEntry();
		$values = array();
		foreach (array_keys($eiEntry->getEiFieldWrappers()) as $eiPropPathStr) {
			$values[$eiPropPathStr] = $this->getEiEntry()->getValue($eiPropPathStr);
		}
		return $values;
	}
	
	/**
	 * @param $eiPropPath
	 * @param $scalarValue
	 * @throws \n2n\util\type\ValueIncompatibleWithConstraintsException
	 */
	public function setScalarValue($eiPropPath, $scalarValue) {
		$eiPropPath = EiPropPath::create($eiPropPath);
		$scalarEiProperty = $this->getEiuFrame()->contextEngine()->getScalarEiProperty($eiPropPath);
		$this->setValue($eiPropPath, $scalarEiProperty->scalarValueToEiFieldValue($scalarValue));
	}
	
	public function getScalarValue($eiPropPath) {
		$eiPropPath = EiPropPath::create($eiPropPath);
		$scalarEiProperty = $this->getEiuFrame()->contextEngine()->getScalarEiProperty($eiPropPath);
		return $scalarEiProperty->eiFieldValueToScalarValue($this->getValue($eiPropPath));
	}
	
	
	
	/**
	 * @param N2nLocale $n2nLocale
	 * @return string
	 */
	public function getGenericLabel(N2nLocale $n2nLocale = null) {
		return $this->mask()->getEiMask()->getLabelLstr()
				->t($n2nLocale ?? $this->eiuAnalyst->getN2nContext(true)->getN2nLocale());
	}

	/**
	 * @param N2nLocale $n2nLocale
	 * @return string
	 */
	public function getGenericPluralLabel(N2nLocale $n2nLocale = null) {
		return $this->mask()->getEiMask()->getPluralLabelLstr()
				->t($n2nLocale ?? $this->eiuAnalyst->getN2nContext(true)->getN2nLocale());
	}
	
	/**
	 * @return string
	 */
	public function getGenericIconType() {
		return $this->getEiuFrame()->getGenericIconType($this);
	}
	
	/**
	 * @param bool $draft
	 * @param mixed $eiTypeArg
	 * @return \rocket\ei\util\entry\EiuEntry
	 */
	public function copy(bool $draft = null, $eiTypeArg = null) {
		return $this->getEiuFrame()->copyEntry($this, $draft, $eiTypeArg);
	}
	
	public function copyValuesTo($toEiEntryArg, array $eiPropPaths = null) {
		$this->getEiuFrame()->copyEntryValuesTo($this, $toEiEntryArg, $eiPropPaths);
	}
	
	/**
	 * @return \rocket\ei\EiEngine
	 */
	public function getEiEngine() {
		return $this->getEiuFrame()->determineEiEngine($this);
	}
	
// 	/**
// 	 * @param mixed $eiPropPath
// 	 * @return boolean
// 	 */
// 	public function containsGuiProp($eiPropPath) {
// 		return $this->eiuFrame->containsGuiProp($eiPropPath);
// 	}
	
// 	/**
// 	 * @param DefPropPath|string $eiPropPath
// 	 * @return \rocket\ei\EiPropPath|null
// 	 */
// 	public function eiPropPathToEiPropPath($eiPropPath) {
// 		return $this->eiuFrame->eiPropPathToEiPropPath($eiPropPath, $this);
// 	}
	
	/**
	 * @param bool $determineEiMask
	 * @param N2nLocale $n2nLocale
	 * @return string
	 */
	public function createIdentityString(N2nLocale $n2nLocale = null) {
		return $this->mask()->engine()->createIdentityString($this, true, $n2nLocale);
	}
	
	/**
	 * @param int $limit
	 * @param int $num
	 * @return \rocket\ei\manage\draft\Draft[]
	 */
	public function lookupDrafts(int $limit = null, int $num = null) {
		return $this->eiuFrame->lookupDraftsByEntityObjId($this->getId(), $limit, $num);
	}
	
	public function acceptsValue($eiPropPath, $value) {
		return $this->getEiEntry()->acceptsValue(EiPropPath::create($eiPropPath), $value);
	}
	
	/**
	 * 
	 * @param mixed $eiPropPath
	 * @param bool $required
	 * @throws UnknownEiFieldExcpetion
	 * @return \rocket\ei\manage\entry\EiFieldWrapper|null
	 */
	public function getEiFieldWrapper($eiPropPath, bool $required = false) {
		try {
			return $this->getEiEntry(true)->getEiFieldWrapper(EiPropPath::create($eiPropPath));
		} catch (UnknownEiFieldExcpetion $e) {
			if ($required) throw $e;
		}
		
		return null;
	}
	
	/**
	 * @param DefPropPath $defPropPath
	 * @param bool $required
	 * @throws EiFieldOperationFailedException
	 * @throws GuiException
	 * @return \rocket\ei\manage\entry\EiFieldWrapper|null
	 */
	public function getEiFieldAbstraction($defPropPath, bool $required = false) {
		$guiDefinition = $this->mask()->engine()->getGuiDefinition();
		try {
			return $guiDefinition->determineEiFieldAbstraction($this->eiuAnalyst->getN2nContext(true),
					$this->getEiEntry(), DefPropPath::create($defPropPath));
		} catch (UnknownEiFieldExcpetion $e) {
			if ($required) throw $e;
		}
	
		return null;
	}
	
	/**
	 * @param mixed $eiTypeArg
	 * @return boolean
	 */
	public function isTypeOf($eiTypeArg) {
		$eiType = EiuAnalyst::buildEiTypeFromEiArg($eiTypeArg, 'eiTypeArg');
		
		return $this->getEiType()->isA($eiType);
	}
	
	
// 	public function isExecutableBy($eiCommandPath) {
// 		return $this->getEiEntry()->isExecutableBy(EiCommandPath::create($eiCommandPath));
// 	}
	
	public function onValidate(\Closure $closure) {
		$this->getEiEntry()->registerListener(new OnValidateMappingListener($closure, $this->eiuAnalyst->getN2nContext(true)));
	}
	
	public function whenValidated(\Closure $closure) {
		$this->getEiEntry()->registerListener(new ValidatedMappingListener($closure));
	}
	
	public function onWrite(\Closure $closure) {
		$this->getEiEntry()->registerListener(new OnWriteMappingListener($closure));
	}
	
	public function whenWritten(\Closure $closure) {
		$this->getEiEntry()->registerListener(new WrittenMappingListener($closure));
	}
	
	
	public function fieldMap($forkEiPropPath = null) {
		$forkEiPropPath = EiPropPath::create($forkEiPropPath);
		$eiFieldMap = $this->eiEntry->getEiFieldMap();
		
		$ids = $forkEiPropPath->toArray();
		while (null !== ($id = array_shift($ids))) {
			$eiFieldMap = $eiFieldMap->get($id)->getForkedEiFieldMap();
		}
		return new EiuFieldMap($eiFieldMap, $this->eiuAnalyst);
	}
	
	/**
	 * @param mixed $forkEiPropPath
	 * @param object $object
	 * @return \rocket\ei\util\entry\EiuFieldMap
	 */
	public function newFieldMap($forkEiPropPath, object $object) {
		return $this->getEiuFrame()->newFieldMap($this, $forkEiPropPath, $object);
	}
	
	/**
	 * @param EiProp $eiProp
	 * @return boolean
	 */
	public function isDraftProp($eiPropPath) {
		$eiPropPath = EiPropPath::create($eiPropPath);
		
		return $this->getEiObject()->isDraft()
				&& $this->getEiEntry(true)->getEiMask()->getEiEngine()->getDraftDefinition()
						->containsEiPropPath($eiPropPath);
	}
	
	/**
	 * @param EiProp $eiProp
	 * @return object
	 */
	public function getForkObject($eiPropPath) {
		$eiPropPath = EiPropPath::create($eiPropPath);
		
		if ($this->isInitialized()) {
			return $this->getEiFieldWrapper($eiPropPath)->getEiFieldMap()->getObject();
		}
			
		return $this->getEiEntry(true)->getEiMask()->getForkObject($eiPropPath->poped(), $this->eiObject); 
	}
	
	/**
	 * @param EiProp $eiProp
	 * @throws EiFieldOperationFailedException
	 * @return NULL|mixed
	 */
	public function readNativValue($eiPropPath) {
		$eiPropPath = EiPropPath::from($eiPropPath);
		
		if ($this->isDraftProp($eiPropPath)) {
			return $this->getEiObject()->getDraft()->getDraftValueMap()->getValue($eiPropPath);
		}
		
		$eiProp = $this->getEiEntry(true)->getEiMask()->getEiPropCollection()->getByPath($eiPropPath);
		$objectPropertyAccessProxy = $eiProp->getObjectPropertyAccessProxy();
		if ($objectPropertyAccessProxy !== null) {
			return $objectPropertyAccessProxy->getValue($this->getForkObject($eiPropPath));
		}
		
		throw new EiFieldOperationFailedException('There is no ObjectPropertyAccessProxy configured for ' . $eiProp);
	}
	
	/**
	 * @param EiProp $eiProp
	 * @param mixed $value
	 * @throws EiFieldOperationFailedException
	 */
	public function writeNativeValue(EiProp $eiProp, $value) {
		$eiPropPath = EiPropPath::from($eiProp);
		
		if ($this->isDraftProp($eiProp)) {
			$this->eiObject->getDraft()->getDraftValueMap()->setValue($eiPropPath);
			return;
		}
		
		$objectPropertyAccessProxy = $eiProp->getObjectPropertyAccessProxy();
		if ($objectPropertyAccessProxy !== null) {
			$objectPropertyAccessProxy->setValue($this->getForkObject($eiProp), $value);
			return;
		}
		
		throw new EiFieldOperationFailedException('There is no ObjectPropertyAccessProxy configured for ' . $eiProp);
	}
	
	/**
	 * @return boolean
	 */
	function isValid() {
		return $this->getEiEntry()->isValid();
	}
	
	/**
	 * @return boolean
	 */
	function isUnsaved() {
		return $this->eiEntry !== null && $this->eiEntry->hasChanges();
	}
	
	/**
	 * @return boolean
	 */
	function save(/*bool $insertIfNew = false*/) {
		if (!$this->eiEntry->save()) {
			return false;
		}
		
		if (!$this->eiEntry->isNew()) {
			return true;
		}
		
		$entityObj = $this->eiEntry->getEiObject()->getEiEntityObj()->getEntityObj();
		$nestedSetStrategy = $this->eiEntry->getEiType()->getNestedSetStrategy();
		$em = $this->eiuAnalyst->getEiFrame(true)->getManageState()->getEntityManager();
		if ($nestedSetStrategy === null) {
			$em->persist($entityObj);
			$em->flush();
			return true;
		}
		
		$nsu = $this->createNestedSetUtils($nestedSetStrategy);
		$nsu->insert($entityObj);
		return true;
	}
	
	/**
	 * @param NestedSetStrategy $nestedSetUtils
	 * @return \n2n\persistence\orm\util\NestedSetUtils
	 */
	private function createNestedSetUtils(NestedSetStrategy $nestedSetStrategy) {
		return new NestedSetUtils($this->eiuAnalyst->getManageState()->getEntityManager(), 
				$this->eiuAnalyst->getEiFrame(true)->getContextEiEngine()->getEiMask()->getEiType()->getEntityModel()->getClass(),
				$nestedSetStrategy);
	}
	
	/**
	 * @throws IllegalStateException
	 * @return \n2n\persistence\orm\util\NestedSetUtils
	 */
	private function valNestedInsertable() {
		if (!$this->eiEntry->isNew()) {
			throw new IllegalStateException('EiEntry is not new.');
		}
		
		$nestedSetStrategy = $this->eiEntry->getEiType()->getNestedSetStrategy();
		if ($nestedSetStrategy === null) {
			throw new IllegalStateException($this->eiEntry->getEiType()->__toString() . ' has no NestedSetStrategy.');
		}
		
		return $this->createNestedSetUtils($nestedSetStrategy);
	}
	
	function insertAfter($eiObjectArg) {
		$eiObject = EiuAnalyst::buildEiObjectFromEiArg($eiObjectArg);
		
		if (!$this->eiEntry->save(false)) {
			return false;
		}
		
		$nsu = $this->valNestedInsertable();
		$nsu->insertAfter($this->eiEntry->getEiObject()->getEiEntityObj()->getEntityObj(), $eiObject->getEiEntityObj()->getEntityObj());
		$this->eiuAnalyst->getManageState()->getEntityManager()->flush();
		return true;
	}
	
	function insertBefore($eiObjectArg) {
		$eiObject = EiuAnalyst::buildEiObjectFromEiArg($eiObjectArg);
		
		if (!$this->eiEntry->save(false)) {
			return false;
		}
		
		$nsu = $this->valNestedInsertable();
		$nsu->insertBefore($this->eiEntry->getEiObject()->getEiEntityObj()->getEntityObj(), $eiObject->getEiEntityObj()->getEntityObj());
		$this->eiuAnalyst->getManageState()->getEntityManager()->flush();
		return true;
	}
	
	function insertAsChild($parentEiObjectArg) {
		$eiObject = EiuAnalyst::buildEiObjectFromEiArg($parentEiObjectArg);
		
		if (!$this->eiEntry->save(false)) {
			return false;
		}
		
		$nsu = $this->valNestedInsertable();
		$nsu->insert($this->eiEntry->getEiObject()->getEiEntityObj()->getEntityObj(), $eiObject->getEiEntityObj()->getEntityObj());
		$this->eiuAnalyst->getManageState()->getEntityManager()->flush();
		return true;
	}
	
	function remove(): TransactionApproveAttempt {
		$ms = $this->eiuAnalyst->getManageState();
		$ms->remove($this->getEiObject());
		return $ms->flush();
	}
	
	/**
	 * @return \rocket\si\content\SiEntryQualifier
	 */
	function createSiEntryQualifier() {
		$siMaskQualifier = $this->mask()->createSiMaskQualifier();
		$idName = $this->createIdentityString();
		
		if ($this->eiuObject !== null) {
			return $this->eiuObject->getEiObject()->createSiEntryIdentifier()->toQualifier($siMaskQualifier, $idName);
		}
		
		return $this->eiEntry->getEiObject()->createSiEntryIdentifier()->toQualifier($siMaskQualifier, $idName);
	}
	
	function getMessages($eiPropPath = null, bool $recursive = false) {
		$eiPropPath = EiPropPath::build($eiPropPath);
		
		$eiEntry = $this->getEiEntry(false);
		if ($eiEntry === null || !$eiEntry->hasValidationResult()) {
			return [];
		}
		
		$validationResult = $eiEntry->getValidationResult();
		if ($eiPropPath === null) {
			return $validationResult->getMessages($recursive);
		}
		
		$fieldValidationResult = $validationResult->getEiFieldValidationResult($eiPropPath);
		if ($fieldValidationResult === null) {
			return [];
		}
		
		return $fieldValidationResult->getMessages($recursive);
	}
	
	function getMessagesAsStrs($eiPropPath = null, bool $recursive = false) {
		return array_map(fn ($m) => $m->t($this->eiuAnalyst->getN2nContext(true)->getN2nLocale()), 
				$this->getMessages($eiPropPath));
	}
}  

// class InitListener implements EiEntryGuiListener {
// 	private $eiEntryGuiAssembler;
	
// 	public function __construct(EiEntryGuiAssembler $eiEntryGuiAssembler) {
// 		$this->eiEntryGuiAssembler = $eiEntryGuiAssembler;
// 	}
	
// 	public function finalized(EiEntryGui $eiEntryGui) {
// 		$eiEntryGui->unregisterEiEntryGuiListener($this);
		
// 		$this->eiEntryGuiAssembler->finalize();
// 	}

// 	public function onSave(EiEntryGui $eiEntryGui) {
// 	}

// 	public function saved(EiEntryGui $eiEntryGui) {
// 	}
// }

class DummyEiGuiSiFactory implements EiGuiSiFactory {
	
	public function getSiStructureDeclarations(): array {
		throw new NotYetImplementedException();
	}

	public function getSiProps(): array {
		throw new NotYetImplementedException();
	}
}
