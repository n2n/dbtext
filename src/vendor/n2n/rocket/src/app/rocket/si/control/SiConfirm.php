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

class SiConfirm implements \JsonSerializable {
	private $message;
	private $okLabel;
	private $cancelLabel;
	private $danger = false;
	
	public function __construct(?string $message, ?string $okLabel, ?string $cancelLabel, bool $danger = false) {
		$this->message = $message;
		$this->okLabel = $okLabel;
		$this->cancelLabel = $cancelLabel;
		$this->danger = $danger;
	}
	
	public function setMessage(?string $message) {
		$this->message = $message;
		return $this;
	}
	
	public function getMessage() {
		return $this->message;
	}
	
	public function setOkLabel(?string $okLabel) {
		$this->okLabel = $okLabel;
		return $this;
	}
	
	public function getOkLabel() {
		return $this->okLabel;
	}
	
	public function setCancelLabel(?string $cancelLabel) {
		$this->cancelLabel = $cancelLabel;
		return $this;
	}
	
	public function getCancelLabel() {
		return $this->cancelLabel;
	}
	
	function setDanger(bool $danger) {
		$this->danger = $danger;
		return $this;
	}
	
	function isDanger() {
		return $this->danger;
	}
	
	public function jsonSerialize() {
		return [
			'message' => $this->message,
			'okLabel' => $this->okLabel,
			'cancelLabel' => $this->cancelLabel,
			'danger' => $this->danger
		];
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
