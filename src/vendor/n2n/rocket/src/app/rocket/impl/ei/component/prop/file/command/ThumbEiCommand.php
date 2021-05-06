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
namespace rocket\impl\ei\component\prop\file\command;

use rocket\impl\ei\component\command\adapter\EiCommandAdapter;
use rocket\impl\ei\component\prop\file\command\controller\ThumbController;
use rocket\ei\util\Eiu;
use n2n\web\http\controller\Controller;
use rocket\impl\ei\component\prop\file\conf\ThumbResolver;

class ThumbEiCommand extends EiCommandAdapter {
	const ID_BASE = 'thumb';
	
	private $thumbResolver;
	
	public function getIdBase(): ?string {
		return self::ID_BASE;
	}
	
	public function getTypeName(): string {
		return 'Thumb';
	}
	
	public function setThubResolver(ThumbResolver $thumbResolver) {
		$this->thumbResolver = $thumbResolver;
	}
		
	public function lookupController(Eiu $eiu): Controller {
		$thumbController = $eiu->lookup(ThumbController::class);
// 		$thumbController->setThubResolver($this->thumbResolver);
		return $thumbController;
	}
}
