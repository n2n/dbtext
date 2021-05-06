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
namespace n2n\io\img\impl;

use n2n\util\ex\NotYetImplementedException;
use n2n\io\IoUtils;
use n2n\io\img\UnsupportedImageTypeException;
use n2n\io\IoException;
use n2n\io\img\ImageSource;

class ImageSourceFactory {
	const MIME_TYPE_JPEG = 'image/jpeg';
	const MIME_TYPE_PNG = 'image/png';
	const MIME_TYPE_GIF = 'image/gif';
	const MIME_TYPE_WEBP = 'image/webp';
	
	private static $extensionMimeTypeMappings = array('png' => self::MIME_TYPE_PNG,
			'jpg' => self::MIME_TYPE_JPEG, 'jpeg' => self::MIME_TYPE_JPEG,
			'gif' => self::MIME_TYPE_GIF, 'webp' => self::MIME_TYPE_WEBP);
// 	/**
// 	 *
// 	 * @param string $extension
// 	 * @throws UnsupportedImageTypeException
// 	 * @return string
// 	*/
// 	public static function getMimeTypeByFileExtension($extension) {
// 		$extension = mb_strtolower($extension);
// 		if (isset(self::$extensionMimeTypeMappings[$extension])) {
// 			return self::$extensionMimeTypeMappings[$extension];
// 		}
	
// 		throw new UnsupportedImageTypeException('Unsupported image file extension \'' . $extension 
// 				. '\' Supported extensions: ' . implode(', ', array_keys(self::$extensionMimeTypeMappings)));
// 	}
	
// 	public static function getExtensionMimeTypeMappings() {
// 		return self::$extensionMimeTypeMappings;
// 	}
	
	/**
	 * @return string[]
	 */
	public static function getSupportedExtensions() {
		return array_keys(self::$extensionMimeTypeMappings);
	}
	
	/**
	 * @return string[]
	 */
	public static function getSupportedMimeTypes() {
		return [self::MIME_TYPE_PNG, self::MIME_TYPE_JPEG, self::MIME_TYPE_GIF, self::MIME_TYPE_WEBP];
	}
	
// 	public static function isImageManagable(Managable $managable) {
// 		try {
// 			return self::getMimeTypeByFileExtension($managable->getOriginalExtension())
// 					== self::getMimeTypeOfManagable($managable);
// 		} catch (UnsupportedImageTypeException $e) {
// 			return false;
// 		} catch (IoException $e) {
// 			return false;
// 		}
// 	}
	

	public static function isFileSupported($fileName) {
		try {
			$size = IoUtils::getimagesize((string) $fileName);
			return in_array($size['mime'], self::$extensionMimeTypeMappings);
		} catch (IoException $e) {
			return false;
		}
	}	
	
	/**
	 * @param string $fileName
	 * @param bool $required
	 * @throws UnsupportedImageTypeException
	 * @throws IoException
	 * @return string|NULL
	 */
	public static function getMimeTypeOfFile($fileName, bool $required = false) {
// 		$prevE = null;
// 		try {
			$size = IoUtils::getimagesize((string) $fileName);
			if (in_array($size['mime'], self::$extensionMimeTypeMappings)) {
				return $size['mime'];
			}
// 		} catch (IoException $e) {
// 			$prevE = $e;
// 			throw $prevE;
// 		}
		
		if (!$required) return null;
		
		throw new UnsupportedImageTypeException('Unsupported image mime type \'' . $size['mime']
				. '\'. Supported mime types: ' . implode(', ', self::$extensionMimeTypeMappings));
	}
	/**
	 *
	 * @param string $fileName
	 * @param string $mimeType
	 * @return ImageSource
	 * @throws UnsupportedImageTypeException
	 */
	public static function createFromFileName($fileName, $mineType) {
		switch ($mineType) {
			case self::MIME_TYPE_PNG:
				return new PngFileImageSource($fileName);
			case self::MIME_TYPE_JPEG:
				return new JpegFileImageSource($fileName);
			case self::MIME_TYPE_GIF:
				return new GifFileImageSource($fileName);
			case self::MIME_TYPE_WEBP:
				return new WebpFileImageSource($fileName);
		}
	
		throw new UnsupportedImageTypeException('Unsupported image mime type \'' . $mineType 
				. '\'. Supported mime types: ' . implode(', ', self::$extensionMimeTypeMappings));
	}
	
	public static function createFromBytes($bytes) {
		throw new NotYetImplementedException();
	}
}
