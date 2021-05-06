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
namespace rocket\impl\ei\component\prop\relation\model\filter;

use n2n\impl\web\dispatch\mag\model\MagAdapter;
use n2n\web\dispatch\map\PropertyPath;
use n2n\impl\web\ui\view\html\HtmlView;
use n2n\web\dispatch\map\bind\BindingDefinition;
use n2n\reflection\property\AccessProxy;
use n2n\web\dispatch\Dispatchable;
use rocket\ei\util\frame\EiuFrame;
use n2n\web\dispatch\map\bind\BindingErrors;
use rocket\core\model\Rocket;
use n2n\impl\web\dispatch\property\ObjectProperty;
use rocket\ei\manage\LiveEiObject;
use n2n\util\type\ArgUtils;
use n2n\web\ui\UiComponent;
use n2n\web\dispatch\property\ManagedProperty;
use rocket\impl\ei\component\prop\relation\model\mag\EntryLabeler;
use n2n\web\dispatch\mag\UiOutfitter;
use rocket\ei\manage\entry\UnknownEiObjectException;

class RelationSelectorMag extends MagAdapter  {
	private $targetEiuFrame;
	private $targetLiveEntries = array();
	private $targetSelectUrlCallback;
	
	public function __construct($propertyName, EiuFrame $targetEiuFrame, \Closure $targetSelectUrlCallback) {
		parent::__construct($propertyName, 'Entry');
		
		$this->targetEiuFrame = $targetEiuFrame;
		$this->targetSelectUrlCallback = $targetSelectUrlCallback;
	}
	/**
	 * {@inheritDoc}
	 * @see \n2n\web\dispatch\mag\Mag::createManagedProperty($accessProxy)
	 */
	public function createManagedProperty(AccessProxy $accessProxy): ManagedProperty {
		return new ObjectProperty($accessProxy, false);
	}
	
	public function setTargetLiveEntries(array $targetLiveEntries) {
		$this->targetLiveEntries = $targetLiveEntries;
	}
	
	public function getTargetLiveEntries(): array {
		return $this->targetLiveEntries;
	}

	public function getFormValue() {
		$relationSelectorForm = new RelationSelectorForm($this->targetEiuFrame);
		$relationSelectorForm->setEntryPids(array_keys($this->targetLiveEntries));
		foreach ($this->targetLiveEntries as $targetPid => $targetEiEntityObj) {
			$relationSelectorForm->getEntryLabeler()->setSelectedIdentityString($targetPid,
					$this->targetEiuFrame->createIdentityString(new LiveEiObject($targetEiEntityObj)));
		}
		return $relationSelectorForm;	
	}
	
	public function setFormValue($formValue) {
		ArgUtils::assertTrue($formValue instanceof RelationSelectorForm);
		
		$targetLiveEntries = $this->targetLiveEntries;
		$this->targetLiveEntries = array();
		
		foreach ($formValue->getEntryPids() as $targetPid) {
			if (isset($targetLiveEntries[$targetPid])) {
				$this->targetLiveEntries[$targetPid] = $targetLiveEntries[$targetPid];
				continue;
			}
			
			try {
				$this->targetLiveEntries[$targetPid] = $this->targetEiuFrame
						->lookupEntry($this->targetEiuFrame->pidToId($targetPid))
						->getEiEntityObj();
			} catch (UnknownEiObjectException $e) {
			}
		}
	}
	
	/**
	 * {@inheritDoc}
	 * @see \n2n\web\dispatch\mag\Mag::setupBindingDefinition($bindingDefinition)
	 */
	public function setupBindingDefinition(BindingDefinition $bindingDefinition) {
		
	}

	/**
	 * {@inheritDoc}
	 * @see \n2n\web\dispatch\mag\Mag::createUiField($propertyPath, $view)
	 */
	public function createUiField(PropertyPath $propertyPath, HtmlView $view, UiOutfitter $uiOutfitter): UiComponent {
		$selectOverviewToolsUrl = $this->targetSelectUrlCallback->__invoke($view->getHttpContext());
		
		return $view->getImport('\rocket\impl\ei\component\prop\relation\view\selectorMag.html',
				array('propertyPath' => $propertyPath, 'selectOverviewToolsUrl' => $selectOverviewToolsUrl));
	}
}

class RelationSelectorForm implements Dispatchable {
	private $entryUtils;
	private $entryLabeler;
	protected $entryPids = array();
	
	public function __construct(EiuFrame $entryUtils) {
		$this->entryUtils = $entryUtils;
		$this->entryLabeler = new EntryLabeler($entryUtils);
	}
	
	public function getEntryPids(): array {
		return $this->entryPids;
	}
	
	public function setEntryPids(array $entryPids) {
		$this->entryPids = $entryPids;
	}
	
	public function getEntryLabeler(): EntryLabeler {
		return $this->entryLabeler;
	}
	
	private function _validation(BindingDefinition $bd) {
		$that = $this;
		$bd->closure(function (array $entryPids, BindingErrors $be) use ($that) {
			foreach ($entryPids as $entryPid) { 
				if (!$that->entryUtils->containsId($that->entryUtils->pidToId($entryPid))) continue;
				
				$be->addErrorCode('entryPid', 'ei_impl_relation_unkown_entry_err', 
						array('id_rep' => $entryPid), Rocket::NS);
			}
		});
	}
}
