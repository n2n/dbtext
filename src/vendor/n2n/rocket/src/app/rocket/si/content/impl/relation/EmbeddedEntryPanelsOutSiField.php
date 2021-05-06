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
namespace rocket\si\content\impl\relation;

use n2n\util\type\ArgUtils;
use n2n\util\uri\Url;
use rocket\si\meta\SiFrame;
use rocket\si\content\impl\OutSiFieldAdapter;

class EmbeddedEntryPanelsOutSiField extends OutSiFieldAdapter {
	/**
	 * @var SiFrame
	 */
	private $frame;
	/**
	 * @var SiPanel[]
	 */
	private $panels;
	
	/**
	 * @param Url $apiUrl
	 * @param SiPanel[] $panels
	 */
	function __construct(SiFrame $frame, array $panels = []) {
		$this->frame = $frame;
		$this->setPanels($panels);
	}

	/**
	 * @param SiPanel[] $panels
	 * @return EmbeddedEntryPanelsInSiField
	 */
	function setPanels(array $panels) {
		ArgUtils::valArray($panels, SiPanel::class);
		$this->panels = $panels;
		return $this;
	}
	
	/**
	 * @return SiPanel[]
	 */
	function getPanels() {
		return $this->panels;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\si\content\SiField::getType()
	 */
	function getType(): string {
		return 'embedded-entries-panels-out';
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\si\content\SiField::getData()
	 */
	function getData(): array {
		return [
			'panels' => $this->panels,
			'frame' => $this->frame
		];
	}
}
