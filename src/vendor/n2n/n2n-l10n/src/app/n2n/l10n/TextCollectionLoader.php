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
namespace n2n\l10n;

use n2n\core\TypeLoader;
use n2n\io\IoUtils;
use n2n\core\TypeNotFoundException;

class TextCollectionLoader {
	const LANG_INI_FILE_SUFFIX = '.ini';
	
	private static $textCollections = array();
	/**
	 * @param string $typeName
	 * @return boolean
	 */
	public static function has($typeName) {
		if (isset(self::$textCollections[$typeName])) return true;
		if (array_key_exists($typeName, self::$textCollections)) return false;
		
		if (!TypeLoader::doesTypeExist($typeName, self::LANG_INI_FILE_SUFFIX)) {
			self::$textCollections[$typeName] = null;
		}
		
		return true;
	}
	/**
	 * @param string $typeName
	 * @return \n2n\l10n\TextCollection
	 * @throws \n2n\core\TypeNotFoundException
	 */
	public static function load($typeName) {
		if (isset(self::$textCollections[$typeName])) {
			return self::$textCollections[$typeName];
		}

		return self::$textCollections[$typeName] = new TextCollection(IoUtils::parseIniFile(
				TypeLoader::getFilePathOfType($typeName, self::LANG_INI_FILE_SUFFIX)));
	}
	/**
	 * @param string $typeName
	 * @return \n2n\l10n\TextCollection
	 */
	public static function loadIfExists($typeName) {
		if (array_key_exists($typeName, self::$textCollections)) {
			return self::$textCollections[$typeName];
		}
		
		try {
			return self::load($typeName);
		} catch (TypeNotFoundException $e) {
			self::$textCollections[$typeName] = null;
		}
	}
	
	public static function emptyCache() {
		self::$textCollections = array();
	}
}
