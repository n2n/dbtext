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
namespace rocket\si;

use n2n\web\http\payload\impl\JsonPayload;
use rocket\si\content\SiGui;
use n2n\util\type\ArgUtils;
use rocket\si\control\SiControl;
use rocket\si\content\SiField;
use rocket\si\meta\SiBreadcrumb;

class SiPayloadFactory extends JsonPayload {
	
	/**
	 * @param SiGui $comp
	 * @param SiControl[] $controls
	 * @return \n2n\web\http\payload\impl\JsonPayload
	 */
	static function create(SiGui $comp, array $breadcrumbs, string $title, array $controls = []) {
		ArgUtils::valArray($breadcrumbs, SiBreadcrumb::class);
		
		return new JsonPayload([
			'title' => $title,
			'breadcrumbs' => $breadcrumbs,
			'comp' => self::buildDataFromComp($comp),
			'controls' => self::createDataFromControls($controls)
		]);
	}
	
	/**
	 * @param SiGui|null $content
	 * @return array
	 */
	static function buildDataFromComp(?SiGui $content) {
		if ($content === null) {
			return null;
		}
		
		return [
			'type' => $content->getTypeName(),
			'data' => $content->getData()
		];
	}
	
	/**
	 * @param array $comps
	 * @return array
	 */
	static function createDataFromContents(array $comps) {
		ArgUtils::valArray($comps, SiGui::class);
		
		$json = [];
		foreach ($comps as $key => $content) {
			$json[$key] = self::buildDataFromComp($content);
		}
		return $json;
	}
	
	/**
	 * @param SiControl[] $fields
	 * @return array
	 */
	static function createDataFromFields(array $fields) {
		ArgUtils::valArray($fields, SiField::class);
		
		$fieldsArr = array();
		foreach ($fields as $key => $field) {
			$fieldsArr[$key] = [
				'type' => $field->getType(),
				'data' => $field->getData()
			];
		}
		return $fieldsArr;
	}
	
	/**
	 * @param SiControl[] $controls
	 * @return array
	 */
	static function createDataFromControls(array $controls) {
		ArgUtils::valArray($controls, SiControl::class);
		
		$controlsArr = array();
		foreach ($controls as $control) {
			$controlsArr[] = [
				'type' => $control->getType(),
				'data' => $control->getData()
			];
		}
		return $controlsArr;
	}
	
}