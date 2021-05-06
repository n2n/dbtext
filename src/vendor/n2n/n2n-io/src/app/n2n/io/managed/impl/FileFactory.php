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
namespace n2n\io\managed\impl;

use n2n\web\http\UploadDefinition;
use n2n\io\IoUtils;
use n2n\io\fs\FsPath;
use n2n\io\IoErrorException;
use n2n\io\UploadedFileExceedsMaxSizeException;
use n2n\io\IncompleteFileUploadException;
use n2n\io\managed\File;
use n2n\io\IoException;

class FileFactory {
	/**
	 * @param mixed $fsPath
	 * @param string $originalName
	 * @return \n2n\io\managed\File
	 */
	public static function createFromFs($fsPath, $originalName = null) {
		$fsPath = FsPath::create($fsPath);
		if ($originalName === null) {
			$originalName = $fsPath->getName();
		}
		return new CommonFile(new FsFileSource($fsPath), $originalName);
	}
	/**
	 * @param UploadDefinition $uploadDefinition
	 * @throws UploadedFileExceedsMaxSizeException
	 * @throws IncompleteFileUploadException
	 * @throws IoErrorException
	 * @return \n2n\io\managed\File
	 */
	public static function createFromUploadDefinition(UploadDefinition $uploadDefinition) {
		if (UPLOAD_ERR_OK == $uploadDefinition->getErrorNo()) {
			return self::createFromFs($uploadDefinition->getTmpName(), $uploadDefinition->getName());
		}
	
		switch($uploadDefinition->getErrorNo()) {
			case UPLOAD_ERR_INI_SIZE:
				throw new UploadedFileExceedsMaxSizeException(IoUtils::determineFileUploadMaxSize(),
						'Uploaded file exceeds the upload max filesize directive in php.ini');
			case UPLOAD_ERR_FORM_SIZE:
				// @todo determine MAX_UPLOAD_SIZE
				throw new UploadedFileExceedsMaxSizeException(null,
						'Uploaded file exceeds the MAX_UPLOAD_SIZE http param');
			case UPLOAD_ERR_PARTIAL:
				throw new IncompleteFileUploadException('Uploaded file was only partially uploaded');
			case UPLOAD_ERR_NO_FILE:
				return null;
			case UPLOAD_ERR_NO_TMP_DIR:
				throw new IoErrorException('Configuration error: UPLOAD_ERR_NO_TMP_DIR');
			case UPLOAD_ERR_CANT_WRITE:
				throw new IoErrorException('Configuration error: UPLOAD_ERR_CANT_WRITE');
			case UPLOAD_ERR_EXTENSION:
				throw new IoErrorException('Configuration error: UPLOAD_ERR_EXTENSION');
			default:
				throw new IoErrorException('Unknown configuration error');
		}
	}
	
	/**
	 * 
	 * @param string $str
	 * @throws \InvalidArgumentException if the data url is corrupted
	 * @throws IoException if file could not be written
	 */
	public static function createFromDataUrl(string $data, File $file) {
		$type = null;
		
		if (!preg_match('/^data:image\/(\w+);base64,/', $data, $type)) {
			throw new \InvalidArgumentException('Data url not supported.');
		}
		
		$data = substr($data, strpos($data, ',') + 1);
		$type = strtolower($type[1]); // jpg, png, gif
			
// 			if (!in_array($type, [ 'jpg', 'jpeg', 'gif', 'png' ])) {
// 				throw new \Exception('invalid image type');
// 			}
			
		$data = base64_decode($data, true);
		
		if ($data === false) {
			throw new \InvalidArgumentException('base64_decode failed');
		}
		
		$file->setOriginalName($file->getOriginalName() . '.' . $type);
		$file->getFileSource()->createOutputStream()->write($data);
		
		return $file;
	}
}
