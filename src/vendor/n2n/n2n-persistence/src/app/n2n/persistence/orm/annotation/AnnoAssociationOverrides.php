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
namespace n2n\persistence\orm\annotation;

use n2n\util\type\ArgUtils;
use n2n\reflection\annotation\ClassAnnotation;
use n2n\reflection\annotation\PropertyAnnotation;
use n2n\reflection\annotation\PropertyAnnotationTrait;
use n2n\reflection\annotation\AnnotationTrait;
use n2n\reflection\annotation\ClassAnnotationTrait;

class AnnoAssociationOverrides implements ClassAnnotation, PropertyAnnotation {
	use ClassAnnotationTrait, PropertyAnnotationTrait, AnnotationTrait;
	
	private $annoJoinColumns;
	private $annoJoinTables;
	
	public function __construct(array $annoJoinColumns = null, array $annoJoinTables = null) {
		$this->annoJoinColumns = (array) $annoJoinColumns;
		ArgUtils::valArray($this->annoJoinColumns, 'n2n\persistence\orm\annotation\AnnoJoinColumn');

		$this->annoJoinTables = (array) $annoJoinTables;
		ArgUtils::valArray($this->annoJoinTables, 'n2n\persistence\orm\annotation\AnnoJoinTable');
	}
	/**
	 * @return AnnoJoinColumn[]
	 */
	public function getAnnoJoinColumns() {
		return $this->annoJoinColumns;
	}
	/**
	 * @return AnnoJoinTable[]
	 */
	public function getAnnoJoinTables() {
		return $this->annoJoinTables;
	}
}
