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
namespace rocket\si\control\impl;

use rocket\si\control\SiControl;
use rocket\si\control\SiButton;
use n2n\util\type\ArgUtils;
use rocket\si\SiPayloadFactory;

class GroupSiControl implements SiControl {
	/**
	 * @var SiButton
	 */
	private $button;
	/**
	 * @var SiControl[]
	 */
	private $controls;
	
	/**
	 * @param SiButton $button
	 * @param SiControl[] $controls
	 */
	function __construct(SiButton $button, array $controls = []) {
		ArgUtils::valArray($controls, SiControl::class);
		$this->button = $button;
		$this->controls = $controls;
	}
	
	function getType(): string {
		return 'group';
	}
	
	function getData(): array {
		return [
			'button' => $this->button,
			'controls' => SiPayloadFactory::createDataFromControls($this->controls)
		];
	}
}
