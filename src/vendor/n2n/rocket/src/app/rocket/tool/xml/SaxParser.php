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
namespace rocket\tool\xml;

use n2n\io\IoUtils;
use n2n\io\fs\FsPath;

class SaxParser {
	private $saxHandler;
	/**
	 * 
	 * @param \n2n\io\fs\FsPath $xmlPath
	 * @param \rocket\tool\xml\SaxHandler $saxHandler
	 * @throws \rocket\tool\xml\SaxParsingException
	 */
	public function parse(FsPath $xmlPath, SaxHandler $saxHandler) {
		$parser = xml_parser_create();
		$this->saxHandler = $saxHandler;
		xml_set_object($parser, $this);
		xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, false);
		xml_set_element_handler($parser, "startElement", "endElement");
		xml_set_character_data_handler($parser, "cdata");
		
		$fileRes = IoUtils::fopen($xmlPath, 'rb');
		while(null != ($data = IoUtils::fread($fileRes, 4096))) {
			if (!xml_parse($parser, $data, feof($fileRes))) {
				throw new SaxParsingException(sprintf("XML error: %s at line %d",
					xml_error_string(xml_get_error_code($parser)),
					xml_get_current_line_number($parser)));
			}
		}
	}
	/**
	 * 
	 * @param resource $parser
	 * @param string $tag
	 * @param array $attrs
	 */
	private function startElement($parser, $tagName, array $attrs) {
		$this->saxHandler->startElement($tagName, $attrs);
	}
	/**
	 * 
	 * @param mixed $parser
	 * @param mixed $cdata
	 */
	private function cdata($parser, $cdata) {
		$this->saxHandler->cdata($cdata);
	}
	/**
	 * 
	 * @param resource $parser
	 * @param string $tag
	 */
	private function endElement($parser, $tagName) {
		$this->saxHandler->endElement($tagName);
	}
}
