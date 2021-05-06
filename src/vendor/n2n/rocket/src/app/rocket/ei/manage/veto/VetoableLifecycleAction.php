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
namespace rocket\ei\manage\veto;

use rocket\ei\manage\EiObject;
use n2n\util\ex\IllegalStateException;
use n2n\l10n\Message;

class VetoableLifecycleAction {
	const TYPE_PERSIST = 'persist';
	const TYPE_UPDATE = 'update';
	const TYPE_REMOVE = 'remove';
	
	private $eiObject;
	private $eiLifecycleMonitor;
	private $type;
	private $approved = null;
	private $vetoReasonMessage = null;
	private $whenApprovedClosures = array();
	
	public function __construct(EiObject $eiObject, EiLifecycleMonitor $lifecycleMonitor, string $type) {
		$this->eiObject = $eiObject;
		$this->eiLifecycleMonitor = $lifecycleMonitor;
		$this->type = $type;
	}
	
	/**
	 * @return boolean
	 */
	public function isPersist() {
		return $this->type == self::TYPE_PERSIST;
	}
	
	/**
	 * @return boolean
	 */
	public function isUpdate() {
		return $this->type == self::TYPE_UPDATE;
	}
	
	/**
	 * @return boolean
	 */
	public function isRemove() {
		return $this->type == self::TYPE_REMOVE;
	}
	
	/**
	 * @return \rocket\ei\manage\veto\EiLifecycleMonitor
	 */
	public function getMonitor() {
		return $this->eiLifecycleMonitor;
	}
	
	/**
	 * @return \rocket\ei\manage\EiObject
	 */
	public function getEiObject() {
		return $this->eiObject;
	}
	
	/**
	 * @return boolean
	 */
	public function isInitialized() {
		return $this->approved !== null;
	}
	
	/**
	 * @param Message $reasonMessage
	 */
	public function prevent(Message $reasonMessage) {
		if ($this->approved) {
			throw new IllegalStateException('LifecycleAction already approved.');
		}
		
		$this->approved = false;
		$this->vetoReasonMessage = $reasonMessage;
	}
	
	/**
	 * 
	 */
	public function approve() {
		if ($this->approved) return;
		
		$this->approved = true;
		$this->vetoReasonMessage = null;
		
		foreach ($this->whenApprovedClosures as $whenApprovedClosure) {
			$whenApprovedClosure();
		}
		$this->whenApprovedClosures = array();
	}
	
	public function hasVeto(): bool {
		return null !== $this->vetoReasonMessage;
	}
	
	public function getReasonMessage() {
		if ($this->vetoReasonMessage !== null) {
			return $this->vetoReasonMessage;
		}
		
		throw new IllegalStateException('Remove action was not vetoed.');
	}
	
	public function executeWhenApproved(\Closure $whenApprovedClosure) {
		$this->whenApprovedClosures[] = $whenApprovedClosure;
	}
}
