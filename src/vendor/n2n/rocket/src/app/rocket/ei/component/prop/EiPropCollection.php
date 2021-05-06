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
namespace rocket\ei\component\prop;

use rocket\ei\component\EiComponentCollection;
use rocket\ei\component\UnknownEiComponentException;
use rocket\ei\mask\EiMask;
use rocket\ei\EiPropPath;

class EiPropCollection extends EiComponentCollection {
	private $eiPropPaths = array();
	
	/**
	 * @param EiMask $eiMask
	 */
	public function __construct(EiMask $eiMask) {
		parent::__construct('EiProp', EiProp::class);
		$this->setEiMask($eiMask);
	}

	/**
	 * @param EiPropPath $eiPropPath
	 * @return EiProp
	 * @throws UnknownEiComponentException
	 */
	public function getByPath(EiPropPath $eiPropPath) {
		return $this->getElementByIdPath($eiPropPath);
	}
	
	/**
	 * @param EiPropPath $eiPropPath
	 * @return EiProp[]
	 */
	public function getForkedByPath(EiPropPath $eiPropPath) {
		return $this->getElementsByForkIdPath($eiPropPath);
	}
	
	/**
	 * @param EiProp $eiProp
	 * @param string $id
	 * @param EiPropPath $forkEiPropPath
	 * @return \rocket\ei\component\prop\EiPropWrapper
	 */
	public function add(EiProp $eiProp, string $id = null, EiPropPath $forkEiPropPath = null) {
		$id = $this->makeId($id, $eiProp);
		
		$eiPropPath = null;
		if ($forkEiPropPath === null) {
			$eiPropPath = new EiPropPath([$id]);
		} else {
			$eiPropPath = $forkEiPropPath->ext($id);
		}
		
		$eiPropWrapper = new EiPropWrapper($eiPropPath, $eiProp, $this);
		
		$this->addElement($eiPropPath, $eiProp);
		
		
		
		return $eiPropWrapper;
	}
	
	/**
	 * @param string $id
	 * @param EiProp $eiProp
	 * @param EiPropPath $forkEiPropPath
	 * @return \rocket\ei\component\prop\EiPropWrapper
	 */
	public function addIndependent(string $id, EiProp $eiProp, EiPropPath $forkEiPropPath = null) {
		$eiPropWrapper = $this->add($eiProp, $id, $forkEiPropPath);
		$this->addIndependentElement($eiPropWrapper->getEiPropPath(), $eiProp);
		return $eiPropWrapper;
	}
}
