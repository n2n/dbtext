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
namespace rocket\si\content\impl\meta;

use n2n\util\type\ArgUtils;

class SiCrumb implements \JsonSerializable {
	const TYPE_ICON = 'icon';
	const TYPE_LABEL = 'label';
	
	const SEVERITY_NORMAL = 'normal';
	const SEVERITY_INACTIVE = 'inactive';
	const SEVERITY_IMPORTANT = 'important';
	const SEVERITY_UNIMPORTANT = 'unimportant';
	
	protected $type;
	protected $label;
	protected $iconClass;
	protected $title;
	protected $severity = self::SEVERITY_NORMAL;
	
	/**
	 * @return string
	 */
	function getType() {
		return $this->type;
	}
	
	/**
	 * @return string
	 */
	function getLabel() {
		return $this->label;
	}
	
	/**
	 * @return string
	 */
	function getIconClass() {
		return $this->iconClass;
	}
	
	/**
	 * @param string $title
	 * @return \rocket\si\content\impl\meta\SiCrumb
	 */
	function setTitle(?string $title) {
		$this->title = $title;
		return $this;
	}
	
	function getTitle() {
		return $this->title;
	}
	
	/**
	 * @param string $severity
	 * @return \rocket\si\content\impl\meta\SiCrumb
	 */
	function setSeverity(?string $severity) {
		ArgUtils::valEnum($severity, self::getSeverities());
		$this->severity = $severity;
		return $this;
	}
	
	function getSeverity() {
		return $this->severity;
	}
	
	static function getSeverities() {
		return [self::SEVERITY_NORMAL, self::SEVERITY_INACTIVE, self::SEVERITY_IMPORTANT, self::SEVERITY_UNIMPORTANT];
	}
	
	/**
	 * @return array
	 */
	function jsonSerialize() {
		return [
			'type' => $this->type,
			'label' => $this->label,
			'iconClass' => $this->iconClass,
			'severity' => $this->severity,
			'title' => $this->title
		];
	}
	
	/**
	 * @param string $label
	 * @return \rocket\si\content\impl\meta\SiCrumb
	 */
	static function createLabel(string $label) {
		$addon = new SiCrumb();
		$addon->type = self::TYPE_LABEL;
		$addon->label = $label;
		return $addon;
	}
	
	/**
	 * @param string $iconClass
	 * @return \rocket\si\content\impl\meta\SiCrumb
	 */
	static function createIcon(string $iconClass) {
		$addon = new SiCrumb();
		$addon->type = self::TYPE_ICON;
		$addon->iconClass = $iconClass;
		return $addon;
	}
}
