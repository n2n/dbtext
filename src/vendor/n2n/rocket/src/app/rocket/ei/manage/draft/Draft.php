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
namespace rocket\ei\manage\draft;

use n2n\util\type\ArgUtils;
use rocket\ei\manage\EiEntityObj;
use n2n\util\ex\IllegalStateException;

class Draft {
	const TYPE_NORMAL = 1;
	const TYPE_PUBLISHED = 2;
	const TYPE_RECOVERY = 4;
	const TYPE_UNLISTED = 8;
	
	private $id;
	private $eiEntityObj;
	private $lastMod;
	private $type = self::TYPE_NORMAL;
	private $userId;
	private $draftValueMap = array();
	
	public function __construct(?int $id, EiEntityObj $eiEntityObj, \DateTime $lastMod, 
			int $userId = null, DraftValueMap $draftValueMap = null) {
		$this->id = $id;
		$this->eiEntityObj = $eiEntityObj;
		$this->lastMod = $lastMod;
		$this->userId = $userId;
		$this->draftValueMap = $draftValueMap ?? new DraftValueMap();
	}
	
	public function isNew() {
		return $this->id === null;
	}
	
	public function getId(bool $required = true) {
		if ($this->id !== null || !$required) {
			return $this->id;
		}
		
		throw new IllegalStateException('Draft is new.');
	}
	
	public function setId($id) {
		$this->id = $id;
	}
	
	public function getEiEntityObj(): EiEntityObj {
		return $this->eiEntityObj;
	}
	
	public function getLastMod(): \DateTime {
		return $this->lastMod;
	}
	
	public function setLastMod(\DateTime $lastMod) {
		$this->lastMod = $lastMod;
	}
	
	public function isPublished(): bool {
		return $this->type == self::TYPE_PUBLISHED;
	}
	
	public function isRevorery(): bool {
		return $this->type == self::TYPE_RECOVERY;
	}
	
	public function isUnlisted() {
		return $this->type == self::TYPE_UNLISTED;
	}
	
	public function setType(int $type) {
		ArgUtils::valEnum($type, self::getTypes());
		$this->type = $type;
	}
	
	public function getType() {
		return $this->type;
	}
	
	/**
	 * @return string[]
	 */
	public static function getTypes() {
		return array(self::TYPE_NORMAL, self::TYPE_PUBLISHED, self::TYPE_RECOVERY, self::TYPE_UNLISTED);
	}
	
	public function getUserId(): int {
		return $this->userId;
	}
	
	public function setUserId(int $userId = null) {
		$this->userId = $userId;
	}
	
	public function getDraftValueMap() {
		return $this->draftValueMap;
	}
	
	public function setDraftedEntityObj($draftedEntityObj) {
		ArgUtils::valObject($draftedEntityObj, false, 'draftedEntityObj');
		$this->draftedEntityObj = $draftedEntityObj;
	}
	
	public function getDraftedEntityObj() {
		return $this->draftedEntityObj;
	}
	
	public function equals($obj) {
		if (!($obj instanceof Draft)) return false;
		
		return $this->getId(false) == $obj->getId(false) && $this->getEiEntityObj()->equals($obj->getEiEntityObj());
	}
	
	public function __toString() {
		return 'Draft' . $this->isNew() ? '[new]' : '#' . $this->getId();
	}
}
