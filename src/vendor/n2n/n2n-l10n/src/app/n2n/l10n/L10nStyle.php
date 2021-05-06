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
namespace n2n\l10n;

class L10nStyle {
	const PLACE_HOLDER_DATETIME_FORMAT_DATE = '{date}';
	const PLACE_HOLDER_DATETIME_FORMAT_TIME = '{time}';
	
	private $defaultDateStyle;
	private $defaultTimeStyle;
	private $inputDateStyle;
	private $inputTimeStyle;
	private $dateTimeFormat;
	
	public function __construct(string $defaultDateStyle = null, string $defaultTimeStyle = null, 
			string $inputDateStyle = null, string $inputTimeStyle = null, string $dateTimeFormat = null) {
		$this->defaultDateStyle = $defaultDateStyle;
		$this->defaultTimeStyle = $defaultTimeStyle;
		$this->inputDateStyle = $inputDateStyle;
		$this->inputTimeStyle = $inputTimeStyle; 
		$this->dateTimeFormat = $dateTimeFormat;
	}

	public function getDefaultDateStyle() {
		return $this->defaultDateStyle;
	}

	public function getDefaultTimeStyle() {
		return $this->defaultTimeStyle;
	}

	public function geDefaultInputDateStyle() {
		return $this->inputDateStyle;
	}

	public function getDefaultInputTimeStyle() {
		return $this->inputTimeStyle;
	}
	
	public function getDateTimeFormat() {
		return $this->dateTimeFormat;
	}
}
