// <?php
// /*
//  * Copyright (c) 2012-2016, Hofmänner New Media.
//  * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
//  *
//  * This file is part of the n2n module ROCKET.
//  *
//  * ROCKET is free software: you can redistribute it and/or modify it under the terms of the
//  * GNU Lesser General Public License as published by the Free Software Foundation, either
//  * version 2.1 of the License, or (at your option) any later version.
//  *
//  * ROCKET is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
//  * the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
//  * GNU Lesser General Public License for more details: http://www.gnu.org/licenses/
//  *
//  * The following people participated in this project:
//  *
//  * Andreas von Burg...........:	Architect, Lead Developer, Concept
//  * Bert Hofmänner.............: Idea, Frontend UI, Design, Marketing, Concept
//  * Thomas Günther.............: Developer, Frontend UI, Rocket Capability for Hangar
//  */
// namespace rocket\ei\manage\entry;

// use rocket\ei\EiCommandPath;
// use n2n\util\col\HashSet;
// use rocket\ei\manage\security\EiCommandAccessRestrictor;

// class WhitelistEiCommandAccessRestrictor implements EiCommandAccessRestrictor {
// 	private $eiCommandPaths;
	
// 	public function __construct() {
// 		$this->eiCommandPaths = new HashSet(EiCommandPath::class);
// 	}
	
// 	/**
// 	 * @return \n2n\util\col\HashSet
// 	 */
// 	public function getEiCommandPaths() {
// 		return $this->eiCommandPaths;	
// 	}
		
// 	/* (non-PHPdoc)
// 	 * @see \rocket\ei\manage\entry\EiCommandAccessRestrictor::isaccessibleBy()
// 	 */
// 	public function isAccessibleBy(EiCommandPath $eiCommandPath): bool {
// 		return $this->eiCommandPaths->contains($eiCommandPath);
// 	}
// }
