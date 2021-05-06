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
namespace rocket\tool\mail\controller;

use n2n\util\DateUtils;
use n2n\io\InvalidPathException;
use rocket\tool\mail\model\MailCenter;
use n2n\core\N2N;
use n2n\core\VarStore;
use n2n\log4php\appender\nn6\AdminMailCenter;
use n2n\context\Lookupable;

class MailArchiveBatchController implements Lookupable {
		const FILE_NAME_PREFIX = 'mail';
	const FILE_NAME_PARTS_SEPERATOR = '-';
	const FILE_EXTENSION = 'xml';
	
	public function index() {
		$this->createMailArchive();
	}
	
	public function _onNewMonth() {
		$this->createMailArchive();
	}
	
	public static function dateToFileName(\DateTime $date, $index = null) {
		$nameParts = array(self::FILE_NAME_PREFIX, $date->format('Y'), $date->format('m'));
		if (null !== $index)  {
			$nameParts[] = $index;
		}
		return implode(self::FILE_NAME_PARTS_SEPERATOR, $nameParts) . '.' . self::FILE_EXTENSION;
	}
	/**
	 * @param string $fileName
	 * @return \DateTime
	 */
	public static function fileNameToDate($fileName) {
		$fileNameParts = explode(self::FILE_NAME_PARTS_SEPERATOR, self::removeFileExtension($fileName));
		if (count($fileNameParts) < 3) return null;
		return DateUtils::createDateTimeFromFormat('Ym',  $fileNameParts[1] . $fileNameParts[2]);
	}
	
	public static function fileNameToIndex($fileName) {
		$fileNameParts = explode(self::FILE_NAME_PARTS_SEPERATOR, self::removeFileExtension($fileName));
		if (count($fileNameParts) < 4) return null;
		return $fileNameParts[3];
	}
	
	public static function removeFileExtension($fileName) {
		return str_replace('.' . self::FILE_EXTENSION, '', $fileName);
	}
	
	private function createMailArchive() {
		$date = new \DateTime();
		$date->setDate($date->format('Y'), $date->format('m'), $date->format('d') - 1);
		$fileName = self::dateToFileName($date);
		for ($i = 1; ; $i++) {
			try {
				MailCenter::requestMailLogFile($fileName);
				$fileName = self::dateToFileName($date, $i);
			} catch (InvalidPathException $e){
				break;
			}
		}
		$archiveFilePath = N2N::getVarStore()->requestFilePath(VarStore::CATEGORY_LOG, N2N::NS,
				AdminMailCenter::LOG_FOLDER, $fileName, true, true);
		$currentMailPath = MailCenter::requestMailLogFile(AdminMailCenter::DEFAULT_MAIL_FILE_NAME);
		$currentMailPath->copyFile($archiveFilePath, N2N::getAppConfig()->io()->getPrivateFilePermission());
		$currentMailPath->delete();
	}
}
