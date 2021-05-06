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
namespace n2n\reflection\annotation;

use n2n\util\type\ArgUtils;

trait AnnotationTrait {
	private $fileName;
	private $line;
	private $subAnnotations = array();
	
	public function getFileName() {
		return $this->fileName;
	}
	
	public function setFileName(string $fileName = null) {
		$this->fileName = $fileName;
		
		foreach ($this->subAnnotations as $subAnnotation) {
			$subAnnotation->setFileName($fileName);
		}
	}
	
	public function getLine() {
		return $this->line;
	}
	
	public function setLine(int $line = null) {
		$this->line = $line;
		
		foreach ($this->subAnnotations as $subAnnotation) {
			$subAnnotation->setLine($line);
		}
	}
	
	protected function registerSubAnnotation(Annotation $subAnnotation) {
		$this->subAnnotations[] = $subAnnotation;

		$subAnnotation->setFileName($this->fileName);
		$subAnnotation->setLine($this->line);
	}
	
	protected function registerSubAnnotations(array $subAnnotations) {
		ArgUtils::valArray($subAnnotations, Annotation::class);
		
		foreach ($subAnnotations as $subAnnotation) {
			$this->registerSubAnnotation($subAnnotation);
		}
	}
}
