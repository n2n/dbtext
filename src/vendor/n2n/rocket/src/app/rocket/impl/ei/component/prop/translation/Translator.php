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
namespace rocket\impl\ei\component\prop\translation;

use n2n\l10n\N2nLocale;
use n2n\util\type\ArgUtils;
use n2n\util\col\ArrayUtils;

class Translator {
	
	public static function find($translatables, N2nLocale ...$n2nLocales) {
		$finder = new TranslatableFinder($translatables, $n2nLocales);
		return $finder->find();
	}
		
	public static function require($translatables, N2nLocale ...$n2nLocales) {
		$finder = new TranslatableFinder($translatables, $n2nLocales);
		
		if (null !== ($translatable = $finder->find())) {
			return $translatable;
		}

		throw new UnavailableTranslationException('No translation available for locales: ' . implode(', ', $n2nLocales));
	}
	
	public static function findAny($translatables, N2nLocale ...$n2nLocales) {
		$finder = new TranslatableFinder($translatables, $n2nLocales);
		
		if (null !== ($translatable = $finder->find())) {
			return $translatable;
		}
		
		$finder->setN2nLocales([N2nLocale::getFallback(), N2nLocale::getDefault()]);
		
		if (null !== ($translatable = $finder->find())) {
			return $translatable;
		}

		return ArrayUtils::first($translatables);
	}
	
	public static function requireAny($translatables, N2nLocale ...$n2nLocales) {
		$finder = new TranslatableFinder($translatables, $n2nLocales);
		
		if (null !== ($translatable = self::findAny($translatables, ...$n2nLocales))) {
			return $translatable;
		}
		
		throw new UnavailableTranslationException('No translations available.');
	}
}

class TranslatableFinder {
	private $translatables;
	private $n2nLocales;

	public function __construct($translatables, array $n2nLocales) {
		ArgUtils::valArrayLike($translatables, Translatable::class);

		$this->translatables = $translatables;
		$this->setN2nLocales($n2nLocales);
	}
	
	/**
	 * @param array $n2nLocales
	 */
	public function setN2nLocales(array $n2nLocales) {
		ArgUtils::valArray($n2nLocales, N2nLocale::class);
		$this->n2nLocales = array_values($n2nLocales);
	}

	private function test(Translatable $translatable) {
		$translationN2nLocale = $translatable->getN2nLocale();
		ArgUtils::valTypeReturn($translationN2nLocale, N2nLocale::class, $translatable, 'getN2nLocale');
		
		foreach ($this->n2nLocales as $key => $n2nLocale) {
			if ($n2nLocale->equals($translationN2nLocale)) {
				return $key;
			}
		}
		
		return null;
	}

	public function find() {
		$bestTranslatable = null;
		$bestKey = null;

		foreach ($this->translatables as $translatable) {
			$key = $this->test($translatable);
			if ($key === null || ($bestKey !== null && $bestKey < $key)) continue;
			
			if ($key === 0) return $translatable;
			
			$bestKey = $key;
			$bestTranslatable = $translatable;
		}
		
		return $bestTranslatable;
	}
}
