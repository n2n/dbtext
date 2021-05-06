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
namespace rocket\si\control;

use n2n\util\uri\Url;

class SiButton implements \JsonSerializable {
	const TYPE_PRIMARY = 'btn btn-primary';
	const TYPE_SECONDARY = 'btn btn-secondary';
	const TYPE_SUCCESS = 'btn btn-success';
	const TYPE_DANGER = 'btn btn-danger';
	const TYPE_INFO = 'btn btn-info';
	const TYPE_WARNING = 'btn btn-warning';
	
	private $name;
	private $type = self::TYPE_SECONDARY;
	private $iconType = SiIconType::ICON_ROCKET;
	private $important = false;
	private $iconImportant = false;
	private $iconAlways = false;
	private $labelAlways = false;
	private $href;

	private $tooltip;
	private $confirm;
	
	private function __construct(string $name, string $iconType = null, string $type = self::TYPE_SECONDARY) {
		$this->name = $name;
		$this->iconType = $iconType ?? SiIconType::ICON_ROCKET;
		$this->type = $type;
	}
	
	public function isImportant(): bool {
		return $this->important;
	}
	
	/**
	 * Button will be colored according to the type color.
	 * @param bool $important
	 * @return \rocket\si\control\SiButton
	 */
	public function setImportant(bool $important) {
		$this->important = $important;
		return $this;
	}
	
	public function getName() {
		return $this->name;
	}
	
	/**
	 * Button text.
	 * @param string $name
	 * @return \rocket\si\control\SiButton
	 */
	public function setName(string $name = null) {
		$this->name = $name;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getType() {
		return $this->type;
	}
	
	/**
	 * @param string $type
	 * @return \rocket\si\control\SiButton
	 */
	public function setType(string $type) {
		$this->type = $type;
		return $this;
	}
	
	/**
	 * @return string
	 */
	public function getIconType() {
		return $this->iconType;
	}
	
	/**
	 * @param string $iconType
	 * @return \rocket\si\control\SiButton
	 */
	public function setIconType(string $iconType) {
		$this->iconType = $iconType;
		return $this;
	}
	
	/**
	 * @return string|null
	 */
	public function getTooltip() {
		return $this->tooltip;
	}
	
	/**
	 * @param string $tooltip
	 * @return \rocket\si\control\SiButton
	 */
	public function setTooltip(?string $tooltip) {
		$this->tooltip = $tooltip;
		return $this;
	}
	
	/**
	 * @return boolean
	 */
	public function isIconImportant() {
		return $this->iconImportant;
	}
	
	/**
	 * Icon will always be colored.
	 * @param bool $iconImportant
	 * @return \rocket\si\control\SiButton
	 */
	public function setIconImportant(bool $iconImportant) {
		$this->iconImportant = $iconImportant;
		return $this;
	}
	
	/**
	 * @return boolean
	 */
	public function isIconAlways() {
		return $this->iconAlways;
	}
	
	/**
	 * Icon will always be displayed.
	 * @param bool $iconImportant
	 * @return \rocket\si\control\SiButton
	 */
	public function setIconAlways(bool $iconAlways) {
		$this->iconAlways = $iconAlways;
		return $this;
	}
	
	/**
	 * @return boolean
	 */
	public function isLabelAlways() {
		return $this->labelAlawys;
	}
	
	/**
	 * Button text will always be displayed.
	 * @param bool $labelAlways
	 * @return \rocket\si\control\SiButton
	 */
	public function setLabelAlways(bool $labelAlways) {
		$this->labelAlawys = $labelAlways;
		return $this;
	}
	
	/**
	 * @param SiConfirm $confirm
	 * @return \rocket\si\control\SiButton
	 */
	public function setConfirm(?SiConfirm $confirm) {
		$this->confirm = $confirm;
		return $this;
	}
	
	/**
	 * @return \rocket\si\control\SiConfirm
	 */
	public function getConfirm() {
		return $this->confirm;
	}

	/**
	 * @return Url|null
	 */
	public function getHref() {
		return $this->href;
	}

	/**
	 * when href set <a href> instead of <button> used.
	 * @param Url $href
	 * @return $this
	 */
	public function setHref(Url $href) {
		$this->href = $href;
		return $this;
	}

	public function jsonSerialize() {
		return [
			'name' => $this->name,
			'tooltip' => $this->tooltip,
			'iconClass' => $this->iconType,
			'btnClass' => $this->type,
			'important' => $this->important,
			'iconImportant' => $this->iconImportant,
			'iconAlways' => $this->iconAlways,
			'labelAlways' => $this->labelAlways,
			'confirm' => $this->confirm,
			'href' => (string) $this->href
		];
	}
	
	/**
	 * @param string $name
	 * @param string $siIconType
	 * @return \rocket\si\control\SiButton
	 */
	static function primary(string $name, string $siIconType = null) {
		return new SiButton($name, $siIconType, self::TYPE_PRIMARY, );
	}
	
	/**
	 * @param string $name
	 * @param string $siIconType
	 * @return \rocket\si\control\SiButton
	 */
	static function secondary(string $name, string $siIconType = null) {
		return new SiButton($name, $siIconType, self::TYPE_SECONDARY);
	}
	
	/**
	 * @param string $name
	 * @param string $siIconType
	 * @return \rocket\si\control\SiButton
	 */
	static function success(string $name, string $siIconType = null) {
		return new SiButton($name, $siIconType, self::TYPE_SUCCESS);
	}
	
	/**
	 * @param string $name
	 * @param string $siIconType
	 * @return \rocket\si\control\SiButton
	 */
	static function danger(string $name, string $siIconType = null) {
		return new SiButton($name, $siIconType, self::TYPE_DANGER );
	}
	
	/**
	 * @param string $name
	 * @param string $siIconType
	 * @return \rocket\si\control\SiButton
	 */
	static function info(string $name, string $siIconType = null) {
		return new SiButton($name, $siIconType, self::TYPE_INFO);
	}
	
	/**
	 * @param string $name
	 * @param string $siIconType
	 * @return \rocket\si\control\SiButton
	 */
	static function warning(string $name, string $siIconType = null) {
		return new SiButton($name, $siIconType, self::TYPE_WARNING);
	}
	
// 	public function toSubmitButton(PropertyPath $propertyPath): UiComponent {
// 		$attrs = $inputField->getAttrs();
// 		$uiButton = new HtmlElement('button', $this->applyAttrs($attrs));
// 		$uiButton->appendContent(new HtmlElement('i', array('class' => $this->iconType), ''));
// // 		$uiButton->appendLn();
// 		$uiButton->appendContent(new HtmlElement('span', null, $this->name));
// 		return $uiButton;
// 	}
}
