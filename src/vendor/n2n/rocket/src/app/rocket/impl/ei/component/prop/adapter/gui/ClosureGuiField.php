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
namespace rocket\impl\ei\component\prop\adapter\gui;

use n2n\util\ex\IllegalStateException;
use rocket\ei\manage\gui\field\GuiField;
use rocket\ei\util\Eiu;
use rocket\si\content\SiField;
use rocket\ei\manage\gui\GuiFieldMap;
use n2n\reflection\magic\MagicMethodInvoker;

class ClosureGuiField implements GuiField {
	/**
	 * @var Eiu
	 */
	private $eiu;
	/**
	 * @var SiField
	 */
	private $siField;
	/**
	 * @var MagicMethodInvoker
	 */
	private $writeMmi;
	
	/**
	 * @param Eiu $eiu
	 * @param SiField $siField
	 * @param \Closure|null $writeClosure
	 */
	public function __construct(Eiu $eiu, SiField $siField, \Closure $writeClosure = null) {
		$this->eiu = $eiu;
		$this->siField = $siField;
		
		if ($siField->isReadOnly() && $writeClosure !== null) {
			throw new \InvalidArgumentException('SiField is not writable. No write closure allowed.');
		}
		
		if ($writeClosure !== null) {
			$this->writeMmi = new MagicMethodInvoker($eiu->getN2nContext()); 
			$this->writeMmi->setClassParamObject(Eiu::class, $eiu);
			$this->writeMmi->setClassParamObject(SiField::class, $siField);
			$this->writeMmi->setClassParamObject(get_class($siField), $siField);
			$this->writeMmi->setMethod(new \ReflectionFunction($writeClosure));
		}
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\gui\field\GuiField::getSiField()
	 */
	function getSiField(): SiField {
		return $this->siField;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\gui\field\GuiField::save()
	 */
	public function save() {
		if ($this->siField->isReadOnly()) {
			throw new IllegalStateException('Can not save ready only GuiField');
		}
		
		if ($this->writeMmi !== null) {
			return $this->writeMmi->invoke();
		}
	}
	
// 	function getContextSiFields(): array {
// 		return [];
// 	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\gui\field\GuiField::getForkGuiFieldMap()
	 */
	function getForkGuiFieldMap(): ?GuiFieldMap {
		return null;
	}
}
