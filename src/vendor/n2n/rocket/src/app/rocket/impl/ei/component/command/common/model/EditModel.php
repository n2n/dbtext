<?php
// /*
//  * Copyright (c) 2012-2016, Hofmänner New Media.
//  * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
//  *
//  * This file is part of the n2n module ROCKET.
//  *
//  * ROCKET is free software: you can redistribute it and/or modify it under the terms of the
//  * GNU Lesser General Public License as published by the Free Software Foundation, either
//  * version 2.1 of the License, or (at your option) any later version.
//  *
//  * ROCKET is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
//  * the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
//  * GNU Lesser General Public License for more details: http://www.gnu.org/licenses/
//  *
//  * The following people participated in this project:
//  *
//  * Andreas von Burg...........:	Architect, Lead Developer, Concept
//  * Bert Hofmänner.............: Idea, Frontend UI, Design, Marketing, Concept
//  * Thomas Günther.............: Developer, Frontend UI, Rocket Capability for Hangar
//  */
// namespace rocket\impl\ei\component\command\common\model;

// use n2n\reflection\annotation\AnnoInit;
// use rocket\ei\util\entry\form\EiuEntryForm;
// use n2n\web\dispatch\Dispatchable;
// use n2n\l10n\MessageContainer;
// use n2n\web\dispatch\annotation\AnnoDispProperties;
// use n2n\web\dispatch\map\bind\BindingDefinition;
// use n2n\util\ex\IllegalStateException;
// use rocket\core\model\Rocket;
// use n2n\web\dispatch\map\PropertyPath;
// use rocket\ei\util\entry\EiuEntry;
// use rocket\ei\util\frame\EiuFrame;

// class EditModel implements Dispatchable {
// 	private static function _annos(AnnoInit $ai) {
// 		$ai->c(new AnnoDispProperties('eiuEntryForm'));
// 	}
		
// 	private $draftingAllowed;
// 	private $publishingAllowed;
		
// 	private $eiuFrame;
// 	private $eiuEntryForm;
// 	private $entryModel;
		
// 	public function __construct(EiuFrame $eiuFrame, $draftingAllowed, $publishingAllowed) {
// 		$this->eiuFrame = $eiuFrame;
// 	}
	
// 	public function initialize(EiuEntry $eiuEntry) {
// 		$this->eiuEntryForm = $this->eiuFrame->entryForm($eiuEntry, new PropertyPath(array('eiuEntryForm')));
		
// 		IllegalStateException::assertTrue(!$this->eiuEntryForm->isChoosable());
// 		$this->entryModel = $this->eiuEntryForm->getChosenEiuEntryTypeForm();
// 	}
	
// // 	public function getEiFrame() {
// // 		return $this->entryManager->getEiFrame();
// // 	}
	
// 	public function setPublishAllowed($publishAllowed) {
// 		$this->publishAllowed = $publishAllowed;
// 	}
	
// 	public function isPublishAllowed() {
// 		return $this->publishAllowed;
// 	}
	
// 	public function isDraftable() {
// 		return $this->draftingAllowed && $this->entryModel->getEiMask()->isDraftingEnabled();
// 	}
	
// 	public function isPublishable() {
// 		return $this->publishingAllowed && $this->entryModel
// 				->getEiEntry()->getEiObject()->isDraft();
// 	}
	
// 	public function getEntryModel() {
// 		return $this->entryModel;
// 	}
		
// 	public function getEiuEntryForm() {
// 		return $this->eiuEntryForm;
// 	}
	
// 	public function setEiuEntryForm(EiuEntryForm $eiuEntryForm) {
// 		$this->eiuEntryForm = $eiuEntryForm;
// 	}
	
// 	private function _validation(BindingDefinition $bd) {
	
// 	}
	
// 	public function save(MessageContainer $messageContainer) {
// 		$eiuEntry = $this->eiuEntryForm->buildEiuEntry();
		
// 		if ($eiuEntry->getEiEntry()->save()) {
// 			$this->eiuFrame->persist($eiuEntry);
// 			return true;
// 		}
		
// 		$messageContainer->addErrorCode('common_form_err', null, null, Rocket::NS);
// 		return false;
// 	}
	
// 	public function quicksave(MessageContainer $messageContainer) {
// 		return $this->save($messageContainer);
// 	}
	
// 	public function saveAndPreview(MessageContainer $messageContainer) {
// 		return $this->save($messageContainer);
// 	}
	
// 	public function saveAsNewDraft(MessageContainer $messageContainer) {
// 		if (!$this->isDraftable()) return null;
		
// 		$eiEntry = $this->eiuEntryForm->buildEiEntry();
// 		$draftedEiEntry = $eiEntry->createDraftedCopy();
		
// 		if ($draftedEiEntry->save()) {
// 			return $draftedEiEntry->getDraft();
// 		}
		
// 		$this->initialize($draftedEiEntry);
		
// 		$messageContainer->addAll($mappingValidationResult->getMessages());
// 		return null;
// 	}
	
// 	public function saveAndPublish(MessageContainer $messageContainer) {
// 		if (!$this->isPublishable()) {
// 			return false;
// 		}
		
// 		$eiEntry = $this->eiuEntryForm->buildEiEntry();
// 		IllegalStateException::assertTrue($eiEntry->getEiObject()->isDraft());
		
// 		return $eiEntry->save();
// 	}
// }
