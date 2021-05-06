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
namespace rocket\impl\ei\component\prop\bool\command;

use rocket\impl\ei\component\prop\bool\OnlineEiProp;
use n2n\web\http\controller\ControllerAdapter;
use rocket\ei\util\EiuCtrl;
use rocket\ajah\JhtmlEvent;
use rocket\ei\util\Eiu;
use n2n\l10n\MessageContainer;

class OnlineController extends ControllerAdapter {
	private $onlineEiProp;
	private $onlineEiCommand;
	private $eiuCtrl;
	private $mc;
	
	public function prepare(EiuCtrl $eiCtrl, MessageContainer $mc) {
		$this->eiuCtrl = $eiCtrl;
		$this->mc = $mc;
	}
	
	public function setOnlineEiProp(OnlineEiProp $onlineEiProp) {
		$this->onlineEiProp = $onlineEiProp;
	}
	
	public function setOnlineEiCommand(OnlineEiCommand $onlineEiCommand) {
		$this->onlineEiCommand = $onlineEiCommand;
	}
	
	public function doOnline($pid) {
		$this->setStatus(true, $pid);
	}
	
	public function doOffline($pid) {
		$this->setStatus(false, $pid);
	}
	
	private function setStatus($status, $pid) {
		$eiuEntry = $this->eiuCtrl->lookupEntry($pid);
		$eiuEntry->setValue($this->onlineEiProp, $status);		
		
		$jhtmlEvent = null; 
		if (!$eiuEntry->getEiEntry()->save()) {
			$this->mc->addAll($eiuEntry->getEiEntry()->getValidationResult()->getMessages());
		} else {
			$jhtmlEvent = JhtmlEvent::ei()->noAutoEvents()->controlSwaped($this->onlineEiCommand->createEntryGuiControl(new Eiu($eiuEntry)));
		}
		
		$this->eiuCtrl->redirectToReferer($this->eiuCtrl->buildRedirectUrl($eiuEntry), $jhtmlEvent);
	}
}
