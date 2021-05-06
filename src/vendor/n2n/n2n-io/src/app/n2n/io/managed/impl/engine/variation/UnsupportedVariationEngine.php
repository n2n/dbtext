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
namespace n2n\io\managed\impl\engine\variation;

use n2n\io\managed\AffiliationEngine;
use n2n\util\ex\IllegalStateException;
use n2n\io\managed\ThumbManager;
use n2n\io\managed\VariationManager;

class UnsupportedAffiliationEngine implements AffiliationEngine {
	
	/**
	 * {@inheritDoc}
	 * @see \n2n\io\managed\AffiliationEngine::hasThumbSupport()
	 */
	public function hasThumbSupport(): bool {
		return false;
	}

	/**
	 * {@inheritDoc}
	 * @see \n2n\io\managed\AffiliationEngine::getThumbManager()
	 */
	public function getThumbManager(): ThumbManager {
		throw new IllegalStateException('No thumb support avaialble.');
	}

	/**
	 * {@inheritDoc}
	 * @see \n2n\io\managed\AffiliationEngine::hasVariationSupport()
	 */
	public function hasVariationSupport(): bool {
		return false;
	}

	/**
	 * {@inheritDoc}
	 * @see \n2n\io\managed\AffiliationEngine::getVariationManager()
	 */
	public function getVariationManager(): VariationManager {
		throw new IllegalStateException('No variation support avaialble.');
	}
	
	/**	
	 * {@inheritDoc}
	 * @see \n2n\io\managed\AffiliationEngine::clear()
	 */
	public function clear() {
	}
}