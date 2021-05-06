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

// class MessageTranslator {
// 	private $dtc;
	
// 	public function __construct($modules, $n2nLocales, bool $includeFallbackN2nLocale = true) {
// 		$this->dtc = new DynamicTextCollection($modules, $n2nLocales, $includeFallbackN2nLocale);
// 	}
	
// 	public function translate(Message $message) {
// 		if (!($message instanceof MessageCode)) {
// 			return $message;
// 		}
		
// 		$text = L10nUtils::translateModuleTextCode($this->dtc, $message->getModuleNamespace(), 
// 				$message->getTextCode(), $message->getArgs(), $message->getNum());
		
// 		return new Message($text, $message->getSeverity());
// 	}
	
// 	public function translateAll(array $messages) {
// 		foreach ($messages as $key => $message) {
// 			$messages[$key] = $this->translate($message);
// 		}
		
// 		return $messages;
// 	}
// }
