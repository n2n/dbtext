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
namespace rocket\si\content\impl;

use n2n\core\container\N2nContext;
use n2n\io\managed\File;
use n2n\util\uri\Url;
use n2n\web\ui\UiComponent;
use rocket\si\content\impl\iframe\IframeOutSiField;
use rocket\si\content\impl\iframe\IframeInSiField;
use rocket\si\content\impl\relation\QualifierSelectInSiField;
use rocket\si\content\impl\relation\EmbeddedEntriesInSiField;
use rocket\si\content\impl\relation\EmbeddedEntryPanelsInSiField;
use rocket\si\content\impl\relation\EmbeddedEntryPanelInputHandler;
use rocket\si\content\impl\relation\EmbeddedEntryInputHandler;
use rocket\si\content\impl\split\SplitContextInSiField;
use rocket\si\content\impl\split\SplitContextOutSiField;
use rocket\si\content\impl\split\SplitPlaceholderSiField;
use rocket\si\meta\SiDeclaration;
use rocket\si\control\SiNavPoint;
use rocket\si\content\SiEntryQualifier;
use rocket\si\meta\SiFrame;
use rocket\si\content\impl\string\CkeInSiField;
use rocket\si\content\impl\meta\SiCrumb;
use rocket\si\content\impl\meta\CrumbOutSiField;
use rocket\si\content\impl\relation\EmbeddedEntriesOutSiField;
use rocket\si\content\impl\relation\EmbeddedEntryPanelsOutSiField;
use rocket\si\content\impl\iframe\IframeData;
use rocket\si\content\impl\date\DateTimeInSiField;
use rocket\si\content\impl\string\StringArrayInSiField;
use rocket\si\content\impl\string\PasswordInSiField;

class SiFields {
	
	/**
	 * @return \rocket\si\content\impl\StringInSiField
	 */
	static function stringIn(?string $value) {
		return new StringInSiField($value);
	}
	
	/**
	 * @return \rocket\si\content\impl\StringOutSiField
	 */
	static function stringOut(?string $value) {
		return new StringOutSiField($value);
	}
	
	/**
	 * @param float|null $value
	 * @return \rocket\si\content\impl\NumberInSiField
	 */
	static function numberIn(?float $value) {
		return new NumberInSiField($value);
	}
	
	/**
	 * @param bool $value
	 * @return \rocket\si\content\impl\BoolInSiField
	 */
	static function boolIn(bool $value) {
		return new BoolInSiField($value);
	}
	
	/**
	 * @return \rocket\si\content\impl\string\CkeInSiField
	 */
	static function ckeIn(?string $value) {
		return new CkeInSiField($value);
	}
	
	/**
	 * @param string[] $options
	 * @param string $value
	 * @return \rocket\si\content\impl\EnumInSiField
	 */
	static function enumIn(array $options, ?string $value) {
		return new EnumInSiField($options, $value);
	}
	
	/**
	 * @param \DateTime $value
	 * @return \rocket\si\content\impl\date\DateTimeInSiField
	 */
	static function dateTimeIn(?\DateTime $value) {
		return new DateTimeInSiField($value);
	}
	
	/**
	 * @param SiFile|null $file
	 * @return \rocket\si\content\impl\FileInSiField
	 */
	static function fileIn(?SiFile $file, Url $apiFieldUrl, \JsonSerializable $apiCallId, SiFileHandler $fileHandle) {
		return new FileInSiField($file, $apiFieldUrl, $apiCallId, $fileHandle);
	}
	
	/**
	 * @param File $file
	 * @return \rocket\si\content\impl\FileOutSiField
	 */
	static function fileOut(?SiFile $file) {
		return new FileOutSiField($file);
	}
	
	/**
	 * @param Url $ref
	 * @param string $label
	 * @param bool $href
	 * @return \rocket\si\content\impl\LinkOutSiField
	 */
	static function linkOut(SiNavPoint $navPoint, string $label) {
		return new LinkOutSiField($navPoint, $label);
	}
	
	/**
	 * @param Url $apiUrl
	 * @param array $values
	 * @param int $min
	 * @param int|null $max
	 * @param SiEntryQualifier[]|null $pickables
	 * @return QualifierSelectInSiField
	 */
	static function qualifierSelectIn(SiFrame $frame, array $values = [], int $min = 0, int $max = null, array $pickables = null) {
		return (new QualifierSelectInSiField($frame, $values))->setMin($min)->setMax($max)->setPickables($pickables);
	}

	/**
	 * @param SiFrame $frame
	 * @param array $values
	 * @return \rocket\si\content\impl\relation\EmbeddedEntriesOutSiField
	 */
	static function embeddedEntriesOut(SiFrame $frame, array $values = []) {
		return new EmbeddedEntriesOutSiField($frame, $values);
	}

	/**
	 * @param SiFrame $frame
	 * @param EmbeddedEntryInputHandler $inputHandler
	 * @param array $values
	 * @param int $min
	 * @param int $max
	 * @return \rocket\si\content\impl\relation\EmbeddedEntriesInSiField
	 */
	static function embeddedEntriesIn(SiFrame $frame, EmbeddedEntryInputHandler $inputHandler, array $values = [], 
			int $min = 0, int $max = null) {
		return (new EmbeddedEntriesInSiField($frame, $inputHandler, $values))->setMin($min)->setMax($max);
	}
	
	/**
	 * @param SiFrame $frame
	 * @param array $panels
	 * @return \rocket\si\content\impl\relation\EmbeddedEntryPanelsInSiField
	 */
	static function embeddedEntryPanelsOut(SiFrame $frame, array $panels = []) {
		return (new EmbeddedEntryPanelsOutSiField($frame, $panels));
	}
	
	/**
	 * @param Url $apiUrl
	 * @param EmbeddedEntryPanelInputHandler $inputHandler
	 * @param array $panels
	 * @return \rocket\si\content\impl\relation\EmbeddedEntryPanelsInSiField
	 */
	static function embeddedEntryPanelsIn(SiFrame $frame, EmbeddedEntryPanelInputHandler $inputHandler, 
			array $panels = []) {
		return (new EmbeddedEntryPanelsInSiField($frame, $inputHandler, $panels));
	}
	
	/**
	 * @param array $options
	 * @return \rocket\si\content\impl\split\SplitContextInSiField
	 */
	static function splitInContext(?SiDeclaration $declaration) {
		return new SplitContextInSiField($declaration);
	}
	
	/**
	 * @return \rocket\si\content\impl\split\SplitContextOutSiField
	 */
	static function splitOutContext(?SiDeclaration $declaration) {
		return new SplitContextOutSiField($declaration);
	}
	
	/**
	 * @return \rocket\si\content\impl\split\SplitPlaceholderSiField
	 */
	static function splitPlaceholder(string $refPropId) {
		return new SplitPlaceholderSiField($refPropId);
	}
	
	/**
	 * @return CrumbOutSiField
	 */
	static function crumbOut(SiCrumb ...$crumbs) {
		$siField = new CrumbOutSiField();
		if (!empty($crumbs)) {
			$siField->addNewGroup($crumbs);
		}
		return $siField;
	}

	/**
	 * @param UiComponent $uiComponent
	 * @param N2nContext $templateN2nContext
	 * @return \rocket\si\content\impl\iframe\IframeOutSiField
	 */
	static function iframeOut(UiComponent $uiComponent, N2nContext $templateN2nContext = null) {
		return new IframeOutSiField($templateN2nContext === null 
				? IframeData::createFromUiComponent($uiComponent)
				: IframeData::createFromUiComponentWithTemplate($uiComponent, $templateN2nContext));
	}
	
	/**
	 * @param Url $url
	 * @return \rocket\si\content\impl\iframe\IframeOutSiField
	 */
	static function iframeUrlOut(Url $url) {
		return new IframeOutSiField(IframeData::createFromUrl($url));
	}

	/**
	 * @param UiComponent $uiComponent
	 * @param N2nContext $templateN2nContext
	 * @return \rocket\si\content\impl\iframe\IframeInSiField
	 */
	static function iframeIn(UiComponent $uiComponent, N2nContext $templateN2nContext = null) {
		return new IframeInSiField($templateN2nContext === null
				? IframeData::createFromUiComponent($uiComponent)
				: IframeData::createFromUiComponentWithTemplate($uiComponent, $templateN2nContext));
	}
	
	/**
	 * @return StringArrayInSiField
	 */
	static function stringArrayIn(array $values) {
		return new StringArrayInSiField($values);
	}
	
	/**
	 * @return \rocket\si\content\impl\string\PasswordInSiField
	 */
	static function passwordIn() {
		return new PasswordInSiField();
	}
}
