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
namespace n2n\validation\build;

use n2n\util\type\ArgUtils;
use n2n\l10n\Message;
use n2n\util\magic\MagicArray;
use n2n\util\magic\MagicContext;
use n2n\l10n\N2nLocale;

class ErrorMap implements MagicArray, \JsonSerializable {
	private $messages = [];
	private $children = [];
	
	function __construct(array $messages = []) {
		$this->setMessages($messages);
	}
	
	/**
	 * @return Message[]
	 */
	function getAllMessages() {
		$messages = $this->messages;
		foreach ($this->children as $child) {
			array_push($messages, ...$child->getAllMessages());
		}
		return $messages;
	}
	
	/**
	 * @return Message[]
	 */
	function getMessages() {
		return $this->messages;
	}
	
	/**
	 * @param Message[] $messages
	 */
	function setMessages(array $messages) {
		ArgUtils::valArray($messages, Message::class);
		$this->messages = $messages;
	}
	
	function addMessage(Message $message) {
		$this->messages[] = $message;
	}
	
	/**
	 * @return ErrorMap[]
	 */
	function getChildren() {
		return $this->children;
	}
	
	/**
	 * @param ErrorMap $children
	 */
	function setChildren(array $children) {
		$this->children = $children;
	}
	
	/**
	 * @param ErrorMap $errorMap
	 */
	function putChild(string $key, ErrorMap $errorMap) {
		$this->children[$key] = $errorMap;
	}
	
	function isEmpty() {
		if (!empty($this->messages)) {
			return false;
		}
		
		foreach ($this->children as $child) {
			if (!$child->isEmpty()) {
				return false;
			}
		}
		
		return true;
	}
	
	function toArray(MagicContext $magicContext): array {
		$n2nLocale = $magicContext->lookup(N2nLocale::class, false);
		
		$messageStrs = [];
		foreach ($this->messages as $key => $message) {
			if ($n2nLocale === null) {
				$messageStrs[$key] = (string) $message;
			} else {
				$messageStrs[$key] = $message->t($n2nLocale);
			}
		}
		
		return [
			'messages' => $messageStrs,
			'properties' => array_map(function ($child) use ($magicContext) { return $child->toArray($magicContext); }, 
					$this->getNotEmptyChildren())
		];
	}
	
	function jsonSerialize() {
		$arr = [];
		
		if (!empty($this->messages)) {
			$arr['messages'] = array_map(function ($message) { return (string) $message; }, $this->messages);
		}
		
		$children = $this->getNotEmptyChildren();
		if (!empty($children)) {
			$arr['properties'] = $children;
		}
		
		return $arr;
	}
	
	/**
	 * @return ErrorMap[];
	 */
	private function getNotEmptyChildren() {
		return array_filter($this->children, function ($child) { return !$child->isEmpty(); });
	}


}