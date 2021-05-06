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

use n2n\web\http\controller\ControllerAdapter;
use n2n\web\http\nav\Murl;
use rocket\tool\mail\model\MailCenter;
use n2n\log4php\appender\nn6\AdminMailCenter;
use n2n\web\http\PageNotFoundException;

class MailCenterController extends ControllerAdapter {
	const ACTION_ARCHIVE = 'archive';
	const ACTION_ATTACHMENT = 'attachment';
	
	public function index($currentPageNum = null) {
		$mailCenter = $this->createMailCenter($currentPageNum);
		$this->forward('..\view\mailCenter.html', array('mailCenter' => $mailCenter,
				'currentFileName' => AdminMailCenter::DEFAULT_MAIL_FILE_NAME));
	}
	
	public function doArchive($fileName, $currentPageNum = null) {
		$mailXmlFilePath = MailCenter::requestMailLogFile($fileName);

		if ($mailXmlFilePath->getExtension() !== 'xml') {
			throw new PageNotFoundException();
		}
		
		$mailCenter = new MailCenter($mailXmlFilePath);
		if (null !== $currentPageNum) {
			$mailCenter->setCurrentPageNum($currentPageNum);
		}
		
		$this->forward('tool\mail\view\mailCenter.html', array('mailCenter' => $mailCenter,
			'currentFileName' => $fileName));
	}

	/**
	 * Sends back the attachment as File
	 *
	 * @param $xmlFilename
	 * @param $mailIndex
	 * @param $attachmentIndex
	 * @param $filename string for correct name during download
	 * @throws \n2n\io\managed\InaccessibleFileSourceException
	 */
	public function doAttachment($xmlFilename, $mailIndex, $attachmentIndex, $filename) {
		$mailXmlFilePath = MailCenter::requestMailLogFile($xmlFilename);
		if ($mailXmlFilePath->getExtension() !== 'xml') {
			throw new PageNotFoundException();
		}
		$mailCenter = new MailCenter($mailXmlFilePath);

		if (null === ($attachment = $mailCenter->getAttachment($mailIndex, $attachmentIndex))) {
			throw new PageNotFoundException();
		}
		if (!$attachment->getFileSource()->getFsPath()->isFile()) {
			throw new PageNotFoundException();
		}

		$this->sendFile($attachment);
	}

	public function doMails($filename, int $currentPageNum = null) {
		$mailCenter = $this->createMailCenter($currentPageNum, $filename);

		$currentItems = $mailCenter->getCurrentItems();
		if ($currentItems == null) {
			$this->sendJson([]);
			return;
		}

		$mailItems = $this->createMailsJsonArray($currentItems, $filename);
		$this->sendJson($mailItems);
	}

	public function doMailsPageCount() {
		$this->sendJson($this->createMailCenter()->getNumPages());
	}

	public function doMailsLogFileDatas() {
		$mailLogFileData = array();

		$fileFsPaths = AdminMailCenter::scanMailLogFiles();
		foreach ($fileFsPaths as $fileFsPath) {
			$filename = $fileFsPath->getName();
			$mailCenter = $this->createMailCenter(null, $filename);
			$mailLogFileData[] = array('filename' => $filename, 'numPages' => $mailCenter->getNumPages());
		}

		$this->sendJson($mailLogFileData);
	}

	private function createMailCenter(int $currentPageNum = null, string $filename = null) {
		if (!AdminMailCenter::logFileExists($filename)) {
			throw new PageNotFoundException();
		}

		if ($filename == null) {
			$filename = AdminMailCenter::DEFAULT_MAIL_FILE_NAME;
		}

		$mailXmlFilePath = MailCenter::requestMailLogFile($filename);
		$mailCenter = new MailCenter($mailXmlFilePath);

		if (null !== $currentPageNum) {
			if ($mailCenter->getNumPages() != 0) {
				$mailCenter->setCurrentPageNum($currentPageNum);
			}
		}

		return $mailCenter;
	}

	private function createMailsJsonArray(array $mailItems, string $filename) {
		foreach ($mailItems as $i => $mailItem) {
			if (empty($mailItem->getAttachments())) continue;
			foreach ($mailItem->getAttachments() as $attachmentI => $attachment) {
				$url = $this->buildUrl(Murl::controller())->pathExt('attachment', $filename, $i, $attachmentI,
						$attachment->getName());
				$attachment->setPath((string) $url);
			}
		}

		$mailItems = array_values($mailItems); // so json_encode doesn't send an object
		return $mailItems;
	}
}
