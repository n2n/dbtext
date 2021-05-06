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
namespace n2n\io\managed\impl\engine;

use n2n\util\StringUtils;
use n2n\io\IoUtils;

class QualifiedNameBuilder {
	const LEVEL_SEPARATOR = '/';
	const QN_MAX_LENGTH = 255;
	const FILE_NAME_MAX_LENGTH = 60;

	const RES_FOLDER_PREFIX = 'res-';
	const RES_FOLDER_PREFIX_REPLACEMENT = 're-';
	
	private static $FORBIDDEN_EXTENSION_NEEDLES = array('php', 'htaccess', 'htpasswd', FileInfoDingsler::INFO_EXTENSION);
	
	private $dirLevelNames;
	private $fileName;
	private $reservedPrefixes = array();
	
	public function __construct(array $dirLevelNames, string $fileName) {
		$this->dirLevelNames = $dirLevelNames;
		$this->fileName = $fileName;
		
		foreach ($this->dirLevelNames as $dirLevelName) {
			$this->validateLevel($dirLevelName);
		}
		
		$this->validateFileName($fileName);
		
		$qualifiedName = $this->__toString();		
		if (mb_strlen($qualifiedName) > self::QN_MAX_LENGTH) {
			throw new QualifiedNameFormatException('Qualified name is too long (' . mb_strlen($qualifiedName)
					. ' chars, max: ' . self::QN_MAX_LENGTH . ' chars): ' . $qualifiedName);
		}
	}
	
	public function getDirLevelNames(): array {
		return $this->dirLevelNames;
	} 
	
	public function getFileName(): string {
		return $this->fileName;
	}
	
	public static function createFromString(string $qualifiedName): QualifiedNameBuilder {
		$dirLevelNames = explode(self::LEVEL_SEPARATOR, $qualifiedName);
		$fileName = array_pop($dirLevelNames);
		return new QualifiedNameBuilder($dirLevelNames, $fileName);
	}
	
	public function toArray(): array {
		return array_merge($this->dirLevelNames, array($this->fileName));
	}
	
	public function __toString(): string {
		return implode(self::LEVEL_SEPARATOR, $this->toArray());		
	}
	
	public static function validateLevel(string $level) {
		if (StringUtils::startsWith(self::RES_FOLDER_PREFIX, $level)) {
			throw new QualifiedNameFormatException('Unallowed level prefix \'' . self::RES_FOLDER_PREFIX
					. '\' in qualified name: ' . $level);
		}
	
		if (IoUtils::hasSpecialChars($level)) {
			throw new QualifiedNameFormatException('Invalid chars in qualified name level: ' . $level);
		}
	
		if (mb_strlen($level) == 0) {
			throw new QualifiedNameFormatException('Io empty level: ' . $level);
		}
	}
	
	private static function validateFileName(string $fileName) {
		self::validateLevel($fileName);
		
		$extension = pathinfo($fileName, PATHINFO_EXTENSION);
		if ($extension === "") {
			$extension = $fileName;
		}
		foreach (self::$FORBIDDEN_EXTENSION_NEEDLES as $needle) {
			if (false === stripos($extension, $needle)) continue;
				
			throw new QualifiedNameFormatException('File extension contains forbidden string \''
					. $needle . '\': ' . $fileName);
		}
	}
	/**
	 * @param string $fileName
	 * @return string
	 * @throws \InvalidArgumentException
	 */
	public static function qualifyFileName(string $fileName): string {
		$fileName = self::stripReservedLevelPrefixes(IoUtils::stripSpecialChars($fileName));
	
		if (!mb_strlen($fileName)) {
			throw new \InvalidArgumentException('File name contains non or only special chars: ' 
					. $fileName);
		}
	
		// shorten
		$fileNameLength = mb_strlen($fileName);
		if ($fileNameLength <= self::FILE_NAME_MAX_LENGTH) {
			return $fileName;
		}
	
		$diff = $fileNameLength - self::FILE_NAME_MAX_LENGTH;
		$fileNameParts = explode('.', $fileName);
	
		$fileNamePartLength = mb_strlen($fileNameParts[0]);
		if ($fileNamePartLength > $diff) {
			$fileNameParts[0] = mb_substr($fileNameParts[0], 0, $fileNamePartLength - $diff);
			return implode('.', $fileNameParts);
		}
	
		return mb_substr($fileName, $diff);
	}
	
	private static function stripReservedLevelPrefixes(string $level): string {
		if (!StringUtils::startsWith(self::RES_FOLDER_PREFIX, $level)) return $level;
	
		return self::RES_FOLDER_PREFIX_REPLACEMENT . mb_substr($level, mb_strlen(self::RES_FOLDER_PREFIX));
	}
	
	private static function valResFolderName($resFolderName) {
		if (!IoUtils::hasSpecialChars($resFolderName)) return;
		
		throw new \InvalidArgumentException('Invalid res folder name: ' . self::RES_FOLDER_PREFIX . $resFolderName);
	}
	
	public static function buildResFolderName(string $resName): string {
		$resFolderName = self::RES_FOLDER_PREFIX . $resName; 
		self::valResFolderName($resFolderName);
		return $resFolderName;
	}
	
	public static function parseResName(string $resFolderName) {
		$prefixLength = mb_strlen(self::RES_FOLDER_PREFIX);
		if (self::RES_FOLDER_PREFIX !== mb_substr($resFolderName, 0, $prefixLength)) {
			return null;
		}

		self::valResFolderName($resFolderName);
		
		return mb_substr($resFolderName, $prefixLength);
	}
}
