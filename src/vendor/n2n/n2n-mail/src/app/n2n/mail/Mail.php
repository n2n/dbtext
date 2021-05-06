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

use n2n\io\managed\File;

class Mail {
	const PRIORITY_HIGHEST = 1;
	const PRIORITY_HIGH = 2;
	const PRIORITY_NORMAL = 3;
	const PRIORITY_LOW = 4;
	const PRIORITY_LOWEST = 5;
	
	const TYPE_TEXT = 'text';
	const TYPE_HTML = 'html';
	
	// simple text only
	const CONTENT_TYPE_PLAIN = 'text/plain';
	// only html text
	const CONTENT_TYPE_HTML = 'text/html';
	// alternative content: plain text and original message in another format like text/html
	const CONTENT_TYPE_ALTERNATIVE = 'multipart/alternative';
	// text plus attachment: contains text/plain and other non-text parts OR reply with a text/plain (reply) and the original messageas 'message/rfc822'
	const CONTENT_TYPE_MIXED = 'multipart/mixed';

	
	//
	// 	SENDER AND RECIPIENTS
	//
	
	private $from;
	private $sender;
	private $replyTo = array();
	private $to = array();
	private $cc = array();
	private $bcc = array();
	private $header = array();
	
	//
	// HEADER
	//
	
	private $subject;
	private $message;
	private $altMessage;
	private $attachments = array();
	private $priority = self::PRIORITY_NORMAL;
	private $messageId;
	private $type; // html or text
	private $charset;
	private $encoding = MailEncoder::ENCODING_8BIT;
	
	public function __construct($from, $subject, $message, $to) {
		$this->setFrom($from);
		$this->setSubject($subject);
		$this->setMessage($message);
		$this->addTo($to);
		$this->messageId = md5(uniqid(time()));
	}
	
	//
	// SENDER AND RECIPIENTS
	//
	
	public function setFrom($from) {
		$this->from = $from instanceof MailAddress ? $from : new MailAddress($from);
	}
	
	
	/**
	 * @return MailAddress
	 */
	public function getFrom() {
		return $this->from;
	}
	
	public function setSender($sender) {
		$this->sender = $sender instanceof MailAddress ? $sender : new MailAddress($sender);
	}
	
	/**
	 * @return MailAddress
	 */
	public function getSender() {
		if (!$this->sender) return $this->getFrom();
		return $this->sender;
	}
	
	public function addReplyTo($replyTo) {
		if (is_array($replyTo)) {
			foreach ($replyTo as $address) {
				$this->addReplyTo($address);
			}
			return;
		}
		$this->replyTo[] = $replyTo instanceof MailAddress ? $replyTo : new MailAddress($replyTo); 
	}
	
	public function getReplyTos() {
		return implode(', ', $this->replyTo);
	}
	
	/**
	 * adds to address(es)
	 * 
	 * @param mixed $to
	 */
	public function addTo($to) {
		if (is_array($to)) {
			foreach ($to as $address) {
				$this->addTo($address);
			}
			return;
		}
		$this->to[] = $to instanceof MailAddress ? $to : new MailAddress($to);
	}
	
	public function getTo() {
		// check if there are to To or Cc addresses
		if ((count($this->to) == 0) && (count($this->cc) == 0)) {
			return 'undisclosed-recipients:;';
		}
		return implode(', ', $this->to);
	}
	
	public function clearTo() {
		$this->to = array();
	}
	
	/**
	 * adds cc address(es)
	 * 
	 * @param mixed $cc
	 */
	public function addCc($cc) {
		if (is_array($cc)) {
			foreach ($cc as $address) {
				$this->addCc($address);
			}
			return;
		}
		$this->cc[] = $cc instanceof MailAddress ? $cc : new MailAddress($cc);
	}
	
	public function getCc() {
		return implode(', ', $this->cc);
	}
	
	public function clearCc() {
		$this->cc = array();
	}
	
	/**
	 * adds bcc address(es)
	 * 
	 * @param mixed $bcc
	 */
	public function addBcc($bcc) {
		if (is_array($bcc)) {
			foreach ($bcc as $address) {
				$this->addBcc($address);
			}
			return;
		}
		$this->bcc[] = $bcc instanceof MailAddress ? $bcc : new MailAddress($bcc);
	}
	
	public function getBcc() {
		return implode(', ', $this->bcc);
	}
	
	public function clearBcc() {
		$this->bcc = array();
	}
	
	public function getRecipients() {
		return array_merge($this->to, $this->cc, $this->bcc);
	}
	
	/**
	 * clears all recipients (to, cc, bcc)
	 */
	public function clearRecipients() {
		$this->clearTo();
		$this->clearCc();
		$this->clearBcc();
	}
	
	//
	// GETTER AND SETTER OF HEADER VALUES
	//
	
	public function setSubject($subject) {
		$this->subject = $subject;
	}
	
	public function getSubject() {
		return $this->subject;
	}
	
	public function setMessage($message) {
		$this->message = $message;
	}
	
	public function getMessage() {
		return $this->message;
	}
	
	public function setMessageId($msgId) {
		if (empty($msgId)) throw new MailException('message id must not be empty');
		$this->messageId = $msgId;
	}
	
	public function getMessageId() {
		return $this->messageId;
	}
	
	public function setAltMessage($altMessage) {
		$this->altMessage = $altMessage;
	}
	
	public function getAltMessage() {
		if (empty($this->altMessage)) {
			return MailEncoder::htmlToText($this->message);
		} 
		return $this->altMessage;
	}
	
	public function addManagable(File $file) {
		$this->addFile($file->getFileSource()->getFsPath(), $file->getOriginalName());
	}
	
	public function addFile(string $path, string $name) {
		if (!is_file($path)) throw new MailException('invalid path');
		$this->attachments[(string) $path] = $name;
	}
	
	public function getAttachments() {
		return $this->attachments;
	}
	
	public function clearAttachments() {
		$this->attachments = array();
	}
	
	public function hasAttachment() {
		return (bool)count($this->attachments);
	}
	
	public function setPriority($priority) {
		$validPrios = array(self::PRIORITY_LOWEST, self::PRIORITY_LOW, self::PRIORITY_NORMAL, self::PRIORITY_HIGH, self::PRIORITY_HIGHEST);
		if (!in_array($priority, $validPrios)) throw new MailException('invalid priority');
		$this->priority = $priority;
	}
	
	public function getPriority() {
		return $this->priority;
	}
	
	/**
	 * sets the type to html or text. other types not allowed --> html mails are convertet to text/alternative
	 * @param string $type
	 * @throws MailException
	 */
	public function setType($type = self::TYPE_TEXT) {
		if (!in_array($type, array(self::TYPE_TEXT, self::TYPE_HTML))) {
			throw new MailException('invalid type: ' . $type);
		}
		$this->type = $type;
	}
	
	public function isHtml() {
		return (bool) $this->type == self::CONTENT_TYPE_HTML;
	}
	
	public function setCharset($charset) {
		$this->charset = $charset;
	}
	
	public function getCharset() {
		if (!$this->charset) {
			$this->charset = mb_internal_encoding();
		}
		return $this->charset;
	}
	
	public function setEncoding($encoding) {
		$validEncodings = array(
				MailEncoder::ENCODING_7BIT, 
				MailEncoder::ENCODING_8BIT, 
				MailEncoder::ENCODING_BASE64, 
				MailEncoder::ENCODING_BINARY, 
				MailEncoder::ENCODING_QUOTED_PRINTABLE);
		if (!in_array($encoding, $validEncodings)) throw new MailException('invalid encoding');
		$this->encoding = $encoding;
	}
	
	public function getEncoding() {
		return $this->encoding;
	}
	
	
	public function addHeaderValue($key, $value) {
		if ($value != trim(preg_replace('/[\r\n]+/', '', $value))) {
			throw new MailException('invalid header value: "' . $value . '"');
		}
		$this->header[$key] = $value;
	}
	
	private function isReadyToSend() {
		// check recipients
		if (count($this->to) + count($this->cc) + count($this->bcc) < 1) throw new MailException('no recipient found - mail not ready to send');	
		// check message
		if (empty($this->message)) throw new MailException('no message found - mail not ready to send');
		
		return true;
	}
	
	public function getHeader($forMail = false) {
		$this->isReadyToSend();
		
		// eol bug fix:
		// the correct end "\r\n" of line does not work on all servers --> see php.net/manual mail function 
		$eol = "\n"; 
		
		// return path
		$header = $this->getHeaderLine('Return-Path', $this->getSender()->getEmail(), $eol); 

		$header .= $this->getHeaderLine('Date', date(\DateTime::RFC2822), $eol);
		
		if (!$forMail) {
			$header .= $this->getHeaderLine('To', $this->getTo(), $eol);
		}
		// add from
		$header	.= $this->getHeaderLine('From', $this->getFrom(), $eol);
		
		// add sender, if different as From
		if ($this->sender && $this->sender != $this->from) {
			$this->getHeaderLine('Sender', $this->getSender(), $eol);
		}
		// add cc recipients, if there are any
		if (count($this->cc)) {
			$header .= $this->getHeaderLine('Cc', $this->getCc(), $eol);
		}
		// add bcc recipients, if there are any
		if (count($this->bcc)) {
			$header .= $this->getHeaderLine('Bcc', $this->getBcc(), $eol);
		}
		// add reply to, if there is one
		if (count($this->replyTo)) {
			$header .= $this->getHeaderLine('Reply-To', $this->getReplyTos(), $eol);
		}
		
		if (!$forMail) {
			$header .= $this->getHeaderLine('Subject', mb_encode_mimeheader($this->getSubject(), $this->getCharset(), 'Q', "\r\n"), $eol);
		}
		
		// get message ID and mailer information
		// @todo: add sending host
		$header .= $this->getHeaderLine('Message-ID', '<' . $this->getMessageId() . '@hnm.ch>', $eol);
		$header .= $this->getHeaderLine('X-Priority', $this->getPriority(), $eol);
		$header .= $this->getHeaderLine('X-Mailer', 'n2n.ch Mailer based on PHP ' . phpversion(), $eol);
		
		// add MIME, Content-Tyoe and Boundary information
		$header .= $this->getHeaderLine('MIME-Version', '1.0', $eol);
		// @todo: escaping?! charset!
		// @todo: insert charset and content type dynamicaly!
		
		$contentType = $this->getContentType();
		if ($contentType == self::CONTENT_TYPE_PLAIN) {
			$header .= $this->getHeaderLine('Content-Type', $contentType . '; charset=' . $this->getCharset(), $eol);
			$header .= $this->getHeaderLine('Content-Transfer-Encoding', $this->getEncoding(), $eol);
		} else {
			$header .= $this->getHeaderLine('Content-Type', $contentType . ";\r\n\tboundary=\"" . $this->getMessageId() . '"', $eol);
		}
		
		// add custom headers
		foreach ($this->header as $key => $value) {
			$header .= $this->getHeaderLine($key, $value, $eol);
		}
		
		return $header;
	}
	
	public function getContentType() {
		if ($this->hasAttachment()) {
			return self::CONTENT_TYPE_MIXED;
		} 
		if ($this->isHtml()) {
			// Mail does not support CONTENT_TYPE_HTML
			return self::CONTENT_TYPE_ALTERNATIVE;
		} else {
			return self::CONTENT_TYPE_PLAIN;
		}
	}
	
	
	private function getHeaderLine($key, $value, $eol = "\n") {
		$value = trim(preg_replace('/[\r\n]+/', '', $value));
		return "{$key}: {$value}{$eol}";
	}
	
	public function getBody() {
		switch ($this->getContentType()) {
			case self::CONTENT_TYPE_PLAIN:
				return MailEncoder::encodeString($this->message, $this->encoding);
			case self::CONTENT_TYPE_ALTERNATIVE:
				return MailEncoder::encodeMultipartAlternative($this->getMessageId(), $this->getMessage(), $this->getAltMessage(), $this->encoding, $this->getCharset());
			case self::CONTENT_TYPE_MIXED:
				$altMessage = $this->isHtml() ? $this->getAltMessage() : null;
				return MailEncoder::encodeMultipartMixed($this->getMessageId(), $this->getMessage(), $altMessage, $this->getAttachments(), $this->encoding, $this->getCharset());
		}
	}
	
	/**
	 * returns the header and the body of the e-mail
	 * 
	 * @return string
	 */
	public function getData() {
		return $this->getHeader() . "\r\n" . $this->getBody();
	}
	

}
