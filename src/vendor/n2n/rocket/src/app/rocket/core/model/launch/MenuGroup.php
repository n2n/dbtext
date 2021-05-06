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
namespace rocket\core\model\launch;

class MenuGroup {
	private $label;
	private $launchPads = array();
	private $labels = array();
	
	public function __construct(string $label) {
		$this->label = $label;
	}
	
	/**
	 * @return string
	 */
	public function getLabel() {
		return $this->label;
	}
	
	/**
	 * @return LaunchPad[] 
	 */
	public function getLaunchPads() {
		return $this->launchPads;
	}
	
	/**
	 * @param LaunchPad $launchPad
	 * @param string $label
	 */
	public function addLaunchPad(LaunchPad $launchPad, string $label = null) {
		$this->launchPads[$launchPad->getId()] = $launchPad;
		$this->labels[$launchPad->getId()] = $label;
	}
	
	/**
	 * @param string $id
	 * @return bool
	 */
	public function containsLaunchPadId(string $id) {
		return isset($this->launchPads[$id]);
	}
	
	/**
	 * @param string $id
	 */
	public function removeLaunchPadById(string $id) {
		unset($this->launchPads[$id]);
	}
	
	/**
	 * @param string $id
	 * @throws UnknownLaunchPadException
	 * @return LaunchPad
	 */
	public function getLaunchPadById(string $id) {
		if (isset($this->launchPads[$id])) {
			return $this->launchPads[$id];
		}
		
		throw new UnknownLaunchPadException($id);
	}
	
	public function determineLabel(LaunchPad $launchPad) {
		if (isset($this->labels[$launchPad->getId()])) {
			return $this->labels[$launchPad->getId()];
		}
		
		return $launchPad->getLabel();
	}
	
	public function getLabelByLaunchPadId(string $id) {
		if (isset($this->labels[$id])) {
			return $this->labels[$id];
		}
		
		return null;
	}
	
}
