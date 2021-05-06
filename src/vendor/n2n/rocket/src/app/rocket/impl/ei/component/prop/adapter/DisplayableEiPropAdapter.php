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
 * Andreas von Burg...........: Architect, Lead Developer, Concept
 * Bert Hofmänner.............: Idea, Frontend UI, Design, Marketing, Concept
 * Thomas Günther.............: Developer, Frontend UI, Rocket Capability for Hangar
 */
namespace rocket\impl\ei\component\prop\adapter;

use rocket\ei\manage\gui\GuiProp;
use rocket\ei\component\prop\GuiEiProp;
use rocket\ei\util\Eiu;
use rocket\ei\manage\gui\ViewMode;
use rocket\ei\manage\gui\field\GuiField;
use rocket\core\model\Rocket;
use rocket\impl\ei\component\prop\adapter\config\DisplayConfig;
use rocket\impl\ei\component\prop\adapter\config\AdaptableEiPropConfigurator;
use rocket\ei\util\factory\EifGuiField;
use n2n\util\ex\UnsupportedOperationException;
use rocket\ei\manage\gui\GuiFieldAssembler;

abstract class DisplayableEiPropAdapter extends IndependentEiPropAdapter implements GuiEiProp, GuiFieldAssembler {
	private $displayConfig;

	/**
	 *
	 * @return DisplayConfig
	 */
	protected function getDisplayConfig() {
		if ($this->displayConfig === null) {
			$this->displayConfig = new DisplayConfig ( ViewMode::all () );
		}

		return $this->displayConfig;
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see \rocket\ei\component\prop\EiProp::isPrivileged()
	 */
	public function isPrivileged(): bool {
		return false;
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see \rocket\impl\ei\component\prop\adapter\IndependentEiPropAdapter::createEiPropConfigurator()
	 */
	protected function createConfigurator(): AdaptableEiPropConfigurator {
		return parent::createConfigurator ()->addAdaption ( $this->getDisplayConfig () );
	}
	
	function buildGuiProp(Eiu $eiu): ?GuiProp {
		return $eiu->factory ()->newGuiProp (function (Eiu $eiu) {
			return $this->getDisplayConfig()->buildGuiPropSetup($eiu, $this);
		})->toGuiProp();
	}
	
	function buildGuiField(Eiu $eiu, bool $readOnly): ?GuiField {
		return $this->createOutEifGuiField ( $eiu, $readOnly )->toGuiField ();
	}
	protected function createOutEifGuiField(Eiu $eiu): EifGuiField {
		throw new UnsupportedOperationException ( get_class ( $this ) . ' must implement  either' . ' createOutEifGuiField(Eiu $eiu): EifGuiField or' . ' buildGuiField(Eiu $eiu, bool $readOnly): ?GuiField.' );
	}
}
