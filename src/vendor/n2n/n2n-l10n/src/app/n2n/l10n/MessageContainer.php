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

use n2n\web\http\payload\impl\Redirect;
use n2n\util\StringUtils;
use n2n\core\N2N;
use n2n\web\http\Response;
use n2n\util\UnserializationFailedException;
use n2n\core\ShutdownListener;
use n2n\context\ThreadScoped;
use n2n\web\http\HttpContext;

class MessageContainer implements ShutdownListener, ThreadScoped {
	const SESSION_KEY = 'messageContainer.messages';
	
	private $messages = array();
	private $httpContext;
	private $response;
	
	private function _init(HttpContext $httpContext = null, Response $response = null) {
		$this->httpContext = $httpContext;
		$this->response = $response;
		
		if (is_null($this->httpContext) || is_null($this->response)) {
			return;
		}
		
		$session = $this->httpContext->getSession();
		if ($session->has(N2N::NS, self::SESSION_KEY)) {
			try {
				$this->messages = (array) StringUtils::unserialize($session->get(N2N::NS, self::SESSION_KEY));
				$session->remove(N2N::NS, self::SESSION_KEY);
			} catch (UnserializationFailedException $e) { }
		}
		
		N2N::registerShutdownListener($this);
	}
	
	public function onShutdown() {
		if (!($this->response->getSentPayload() instanceof Redirect)) return;
		$this->httpContext->getSession()->set(N2N::NS, self::SESSION_KEY, serialize($this->messages));
	}
	
	/**
	 * adds a Messsage object 
	 *  
	 * @param Message $message	the Message object. 
	 * @param string $groupName	the name of the group, which helps to categorize messages. 
	 */
	public function add(Message $message, $groupName = null) {
		if (!isset($this->messages[$groupName])) {
			$this->messages[$groupName] = array();
		}	
		
		$this->messages[$groupName][] = $message;
	}
	
	/**
	 * Adds a collection of message objects.
	 * 
	 * @param array $messages
	 * @param string $groupName
	 */
	public function addAll(array $messages, $groupName = null) {
		foreach ($messages as $message) {
			$this->add($message, $groupName);
		}
	}
	/**
	 * 
	 * @param string $groupName
	 * @return array
	 */
	public function getAll($groupName = null, $severity = null) {
		if (!isset($this->messages[$groupName])) return array();
		
		if (is_null($severity)) {
			return $this->messages[$groupName];
		}
		
		$messages = array();
		foreach ($this->messages[$groupName] as $message) {
			if ($message->getSeverity() & $severity) {
				$messages[] = $message;
			} 
		}
		return $messages;
	}
	
	public function addInfo($text, string $groupName = null) {
		$this->add(Message::create($text, Message::SEVERITY_INFO), $groupName);
	}
	
	public function addInfoCode($code, array $args = null, string $groupName = null, $module = null) {
		$this->add(Message::createCodeArg($code, $args, Message::SEVERITY_INFO, $module), $groupName);
	}
	
	public function addWarn($text, string $groupName = null) {
		$this->add(Message::create($text, Message::SEVERITY_WARN), $groupName);
	}
	
	public function addWarnCode($code, array $args = null, string $groupName = null, $module = null) {
		$this->add(Message::createCodeArg($code, $args, Message::SEVERITY_WARN, $module), $groupName);
	}
	
	public function addError($text, string $groupName = null) {
		$this->add(Message::create($text, Message::SEVERITY_ERROR), $groupName);
	}
	
	public function addErrorCode($code, array $args = null, string $groupName = null, $module = null) {
		$this->add(Message::createCodeArg($code, $args, Message::SEVERITY_ERROR, $module), $groupName);
	}
	
	/**
	 * @param string|null $groupName
	 */
	public function clear(string $groupName = null) {
		if ($groupName === null) {
			$this->messages = array();
			return;
		}
		
		unset($this->messages[$groupName]);
	}
}
