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
namespace rocket\impl\ei\component\prop\string\cke\model;

use n2n\context\Lookupable;
use rocket\si\content\impl\string\CkeStyle;

interface CkeCssConfig extends Lookupable {
	
// 	/**
// 	 * Urls to css files which get added in the head section of the wysiwyg iframe
// 	 * @param HtmlView $view
// 	 * @return Url[]
// 	 */
// 	public function getContentCssUrls(Eiu $eiu): ?array;
	
	/**
	 * Id for the wysiwig iframe body
	 * @return string|NULL
	 */
	public function getBodyId(): ?string;
	
	/**
	 * Additional class name for the wysiwig iframe body
	 * @return string|NULL
	 */
	public function getBodyClass(): ?string;
	
// 	function getStyles(): array;
	
	/**
	 * @return CkeStyle
	 */
	public function getAdditionalStyles(): ?array;
	
	/**
	 * returns an array of the format tags possible tags are 
	 * ("p", "h1", "h2", "h3", "h4", "h5", "h6", "pre", "address")
	 * 
	 * @return array<string>
	 */
	public function getFormatTags(): ?array;
}
