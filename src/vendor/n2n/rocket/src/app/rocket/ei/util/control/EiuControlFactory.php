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
namespace rocket\ei\util\control;

use rocket\si\control\SiButton;
use rocket\ei\util\EiuAnalyst;
use n2n\util\uri\Url;

class EiuControlFactory {
	private $eiuAnalyst;
	
	public function __construct(EiuAnalyst $eiuAnalyst) {
		$this->eiuAnalyst = $eiuAnalyst;
	}
	
	/**
	 * @param string $id
	 * @param SiButton $siButton
	 * @param mixed|null $urlExt
	 * @return \rocket\ei\util\control\EiuRefGuiControl
	 */
	public function newCmdRef(string $id, SiButton $siButton, $urlExt = null) {
		return new EiuRefGuiControl($id, $this->eiuAnalyst->getEiuFrame(true), Url::create($urlExt), $siButton, false);
	}
	
	/**
	 * @param string $id
	 * @param SiButton $siButton
	 * @param mixed|null $urlExt
	 * @return \rocket\ei\util\control\EiuRefGuiControl
	 */
	public function newCmdHref(string $id, SiButton $siButton, $urlExt = null) {
		return new EiuRefGuiControl($id, $this->eiuAnalyst->getEiuFrame(true), Url::create($urlExt), $siButton, true);
	}
	
	/**
	 * @param string $id
	 * @param SiButton $siButton
	 * @param \Closure $callback
	 * @return \rocket\ei\util\control\EiuCallbackGuiControl
	 */
	public function newCallback(string $id, SiButton $siButton, \Closure $callback) {
		return new EiuCallbackGuiControl($id, $this->eiuAnalyst->getEiuFrame(true), $callback, $siButton);
	}
	
	/**
	 * @param string $id
	 * @param SiButton $siButton
	 * @return EiuGroupGuiControl
	 */
	public function newGroup(string $id, SiButton $siButton) {
		return new EiuGroupGuiControl($id, $siButton);
	}
	
	/**
	 * @param string $id
	 * @param SiButton $siButton
	 * @return \rocket\ei\util\control\EiuDeactivatedGuiControl
	 */
	public function newDeactivated(string $id, SiButton $siButton) {
		return new EiuDeactivatedGuiControl($id, $siButton);
	}
}