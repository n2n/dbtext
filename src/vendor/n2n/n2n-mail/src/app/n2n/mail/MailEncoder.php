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
namespace n2n\mail;

use n2n\io\IoUtils;
use n2n\io\fs\FsPath;

class MailEncoder {
	// valid encoding options
	const ENCODING_8BIT = '8bit';
	const ENCODING_7BIT = '7bit';
	const ENCODING_BINARY = 'binary';
	const ENCODING_BASE64 = 'base64';
	const ENCODING_QUOTED_PRINTABLE = 'quoted-printable';
	
	/**
	 * converts html into text
	 * @param string $html
	 * @param string $eol
	 */	
	public static function htmlToText($html, $eol = "\n") {
		// strip parts before body tag
		if (strpos($html, '<body>')){
			$html = substr($html, (strpos($html, '<body>')+6));
		}
		// remove all new lines
		$html = str_replace(array("\r\n", "\n", "\r"), '', $html);
		
		// convert br and p strip html tags
		$html = str_replace(array('<p>', '<div>', '<pre>', '<table>', '<ul>', '<ol>'), $eol . $eol, $html);
		$html = str_replace(array('<br />', '<br>', '<p>', '<ul>', '<li>'), $eol, $html);
		return strip_tags($html);
	}
	
	/**
	 * encodes a string to the given encoding
	 * @param string $string
	 * @param string $encoding
	 * @param string $eol
	 * @throws MailException
	 */
	public static function encodeString($string, $encoding, $eol = "\n") {
		$encoding = strtolower($encoding);
		switch($encoding) {
			case self::ENCODING_BASE64:
				return chunk_split(base64_encode($string), 76, $eol);
			case self::ENCODING_7BIT:
			case self::ENCODING_8BIT:
				$encoded = self::cleanEol($string, $eol);
				if (substr($encoded, - (strlen($eol))) != $eol) $encoded .= $eol;
				return $encoded;
			case self::ENCODING_BINARY:
				return $string;
			case self::ENCODING_QUOTED_PRINTABLE:
				return quoted_printable_encode($string);
			default:
				throw new MailException('invalid encoding type');
		}
	}
	
	/**
	 * returns a propper multipart/alternative block
	 * @param string $boundary
	 * @param string $message
	 * @param string $altMessage
	 * @param string $encoding
	 * @param string $charset
	 * @param string $eol
	 */
	public static function encodeMultipartAlternative($boundary, $message, $altMessage, $encoding, $charset, $eol = "\n") {
		// add text plain
		$string = $eol . $eol . self::encodeBoundaryBlock($boundary, Mail::CONTENT_TYPE_PLAIN, $charset, $encoding, $altMessage, $eol);
		
		// add html
		$string .= self::encodeBoundaryBlock($boundary, Mail::CONTENT_TYPE_HTML, $charset, $encoding, $message, $eol);
		// end string
		$string .= "{$eol}--{$boundary}--{$eol}";
		return $string;
	}
	
	/**
	 * encodes multipart 
	 * @param string $boundary
	 * @param string $message
	 * @param string $altMessage
	 * @param array $attachments
	 * @param string $encoding
	 * @param string $charset
	 * @param string $eol
	 */
	public static function encodeMultipartMixed($boundary, $message, $altMessage, array $attachments, $encoding, $charset, $eol = "\n") {
		$string = $eol . $eol . '--' . $boundary . $eol;
		
		if ($altMessage) {
			// alternative mail with attachment --> get alternative parts
			$altBoundary = 'alt-' . $boundary;
			$string .= 'Content-Type: ' . Mail::CONTENT_TYPE_ALTERNATIVE . '; boundary="' . $altBoundary . "\"" . $eol 
				. self::encodeMultipartAlternative($altBoundary, $message, $altMessage, $encoding, $charset, $eol);
		} else {
			// text mail with attachment --> get text part
			$string .= 'Content-Type: ' . Mail::CONTENT_TYPE_PLAIN . '; charset="' . $charset . '"' . $eol . $eol
				. self::encodeString($message, $encoding, $eol)
				.$eol . $eol;
		}
		
		foreach ($attachments as $path => $name) {
			$abstrPath = new FsPath($path);
			$string .= self::encodeAttachment($boundary, $abstrPath, $name, $charset, $encoding, $eol);
		}
		
		$string .= $eol . $eol . '--' . $boundary . '--' . $eol;
		
		return $string;
	}
	
	/**
	 * encodes a boundary block
	 * @param string $boundary
	 * @param string $contentType
	 * @param string $charset
	 * @param string $encoding
	 * @param string $content
	 * @param string $eol
	 */
	private static function encodeBoundaryBlock($boundary, $contentType, $charset, $encoding, $content, $eol = "\n") {
		return '--' . $boundary . $eol
			. 'Content-Type: ' . $contentType. '; charset="' . $charset . '"' . $eol
			. 'Content-Transfer-Encoding: ' . $encoding . $eol . $eol 
			. self::encodeString($content, $encoding, $eol)
			. $eol . $eol;
	}
	
	/**
	 * encodes an attachment
	 * @param string $boundary
	 * @param FsPath $path
	 * @param string $fileName
	 * @param string $charset
	 * @param string $encoding
	 * @param string $eol
	 */
	private static function encodeAttachment($boundary, FsPath $path, $fileName, $charset, $encoding, $eol = "\n") {
		$file = new FsPath($fileName);
		$string = "--{$boundary}{$eol}"
			. "Content-Type: " . self::getMimeType($file->getExtension()) . "; name=\"" . mb_encode_mimeheader($file->getName(), $charset, 'Q', $eol) . "\"" . $eol
			. "Content-Disposition: attachment; filename=\"" . mb_encode_mimeheader($file->getName(), $charset, 'Q', $eol) . "\"" . $eol
			. "Content-Transfer-Encoding: " . self::ENCODING_BASE64 . $eol . $eol;
		$string .= self::encodeString(IoUtils::getContents($path), 'base64', $eol);
		return $string;
	}
	
	/**
	 * changes every line end from CR or LF to CRLF
	 * @param string $string
	 */
	private static function cleanEol($string, $eol = "\n") {
		$string = str_replace(array("\r\n", "\r"), "\n", $string);
		return str_replace("\n", $eol, $string);
	}
	
	/**
	 * returns a mime type of a file
	 * @param string $ext
	 */
	public static function getMimeType($ext) {
		$ext = strtolower($ext);
		$mimes = array('hqx' => 'application/mac-binhex40', 'cpt' => 'application/mac-compactpro', 'doc' => 'application/msword', 
			'bin' => 'application/macbinary', 'dms' => 'application/octet-stream', 'lha' => 'application/octet-stream', 
			'lzh' => 'application/octet-stream', 'exe' => 'application/octet-stream', 'class' => 'application/octet-stream', 
			'psd' => 'application/octet-stream', 'so' => 'application/octet-stream', 'sea' => 'application/octet-stream', 
			'dll' => 'application/octet-stream', 'oda' => 'application/oda', 'pdf' => 'application/pdf', 
			'ai' => 'application/postscript', 'eps' => 'application/postscript', 'ps' => 'application/postscript', 
			'smi' => 'application/smil', 'smil' => 'application/smil', 'mif' => 'application/vnd.mif', 
			'xls' => 'application/vnd.ms-excel', 'ppt' => 'application/vnd.ms-powerpoint', 'wbxml' => 'application/vnd.wap.wbxml', 
			'wmlc' => 'application/vnd.wap.wmlc', 'dcr' => 'application/x-director', 'dir' => 'application/x-director', 
			'dxr' => 'application/x-director', 'dvi' => 'application/x-dvi', 'gtar' => 'application/x-gtar', 
			'php' => 'application/x-httpd-php', 'php4' => 'application/x-httpd-php', 'php3' => 'application/x-httpd-php', 
			'phtml' => 'application/x-httpd-php', 'phps'  => 'application/x-httpd-php-source', 'js' => 'application/x-javascript', 
			'swf' => 'application/x-shockwave-flash', 'sit' => 'application/x-stuffit', 'tar' => 'application/x-tar', 
			'tgz' => 'application/x-tar', 'xhtml' => 'application/xhtml+xml', 'xht' => 'application/xhtml+xml', 'zip' => 'application/zip', 
			'mid' => 'audio/midi', 'midi' => 'audio/midi', 'mpga' => 'audio/mpeg', 'mp2' => 'audio/mpeg', 'mp3' => 'audio/mpeg', 
			'aif' => 'audio/x-aiff', 'aiff' => 'audio/x-aiff', 'aifc' => 'audio/x-aiff', 'ram' => 'audio/x-pn-realaudio', 
			'rm' => 'audio/x-pn-realaudio', 'rpm' => 'audio/x-pn-realaudio-plugin', 'ra' => 'audio/x-realaudio', 
			'rv' => 'video/vnd.rn-realvideo', 'wav' => 'audio/x-wav', 'bmp' => 'image/bmp', 'gif' => 'image/gif', 'jpeg' => 'image/jpeg', 
			'jpg' => 'image/jpeg', 'jpe' => 'image/jpeg', 'png' => 'image/png', 'tiff' => 'image/tiff', 'tif' => 'image/tiff', 
			'css' => 'text/css', 'html' => 'text/html', 'htm' => 'text/html', 'shtml' => 'text/html', 'txt' => 'text/plain', 
			'text' => 'text/plain', 'log' => 'text/plain', 'rtx' => 'text/richtext', 'rtf' => 'text/rtf', 'xml' => 'text/xml', 
			'xsl' => 'text/xml', 'mpeg' => 'video/mpeg', 'mpg' => 'video/mpeg', 'mpe' => 'video/mpeg', 'qt' => 'video/quicktime', 
			'mov' => 'video/quicktime', 'avi' => 'video/x-msvideo', 'movie' => 'video/x-sgi-movie', 'doc' => 'application/msword', 
			'word' => 'application/msword', 'xl' => 'application/excel', 'eml' => 'message/rfc822');
		return (!isset($mimes[$ext])) ? 'application/octet-stream' : $mimes[strtolower($ext)];
	}
}
