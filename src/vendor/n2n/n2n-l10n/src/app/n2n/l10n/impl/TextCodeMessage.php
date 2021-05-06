<?php
/*
 * Copyright (c) 2012-2016, Hofmänner New Media.
 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
 *
 * This file is part of the N2N FRAMEWORK.
 *
 * The N2N FRAMEWORK is free software: you can redistribute it and/or modify it under the terms of
 * the GNU Lesser General Public License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * N2N is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
 * the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details: http://www.gnu.org/licenses/
 *
 * The following people participated in this project:
 *
 * Andreas von Burg.....: Architect, Lead Developer
 * Bert Hofmänner.......: Idea, Frontend UI, Community Leader, Marketing
 * Thomas Günther.......: Developer, Hangar
 */
namespace n2n\l10n\impl;

use n2n\util\StringUtils;
use n2n\l10n\Message;
use n2n\l10n\N2nLocale;
use n2n\l10n\DynamicTextCollection;
use n2n\l10n\L10nUtils;
use n2n\l10n\TextCollection;

class TextCodeMessage extends Message {
	private $textCode;
	private $args;
	private $num;
	private $moduleNamespace;
	
	public function __construct(string $textCode, array $args = null, int $severity = null, string $moduleNamespace = null, int $num = null) {
		parent::__construct($severity);
		
		$this->textCode = $textCode;
		$this->args = (array) $args;
		$this->num = $num;
		$this->moduleNamespace = $moduleNamespace;
	}
	
	public function setTextCode(string $textCode) {
		$this->textCode = $textCode;
		return $this;
	}
	
	public function getTextCode() {
		return $this->textCode;
	}
	
	public function getArgs() {
		return $this->args;
	}
	
	public function setArgs(array $args) {
		$this->args = $args;
		return $this;
	}
	
	public function getNum() {
		return $this->num;
	}
	
	public function setNum(?int $num) {
		$this->num = $num;
		return $this;
	}
	
	public function setModuleNamespace(?string $moduleNamespace) {
		$this->moduleNamespace = $moduleNamespace;
		return $this;
	}
	
	public function getModuleNamespace() {
		return $this->moduleNamespace;
	}
	
	public function t(N2nLocale $n2nLocale, string $moduleNamespace = null): string {
		$dtc = new DynamicTextCollection($this->moduleNamespace, $n2nLocale);
		return $this->tByDtc($dtc, $n2nLocale, $moduleNamespace);
	}
		
	public function tByDtc(DynamicTextCollection $dtc): string {
		return L10nUtils::translateModuleTextCode($dtc, $this->moduleNamespace, $this->textCode, $this->args, $this->num);
	}
	
	public function __toString(): string {
		return StringUtils::pretty(TextCollection::implode($this->textCode, $this->args));
	}
}
