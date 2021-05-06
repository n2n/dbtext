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

use DateTime;
use JsonSerializable;
use n2n\l10n\L10nUtils;
use n2n\l10n\N2nLocale;

class MailItem implements JsonSerializable {
	
	private $dateTime;
	private $to = '';
	private $from = '';
	private $cc = '';
	private $bcc = '';
	private $replyTo = '';
	private $attachments = array();
	private $message = '';
	private $subject = '';

	/**
	 * @param DateTime $dateTime
	 */
	public function __construct(DateTime $dateTime) {
		$this->dateTime = $dateTime;
	}
	
	/**
	 * @return DateTime
	 */
	public function getDateTime() {
		return $this->dateTime;
	}
	public function getTo() {
		return $this->to;
	}

	public function setTo($to) {
		$this->to .= $to;
	}

	public function getFrom() {
		return $this->from;
	}

	public function setFrom($from) {
		$this->from .= $from;
	}

	public function getCc() {
		return $this->cc;
	}

	public function setCc($cc) {
		$this->cc .= $cc;
	}

	public function getBcc() {
		return $this->bcc;
	}

	public function setBcc($bcc) {
		$this->bcc .= $bcc;
	}

	public function getReplyTo() {
		return $this->replyTo;
	}

	public function setReplyTo($replyTo) {
		$this->replyTo .= $replyTo;
	}
	
	public function hasReplyTo() {
		return (bool) trim($this->replyTo);
	}

	public function getAttachments() {
		return $this->attachments;
	}

	public function setAttachments($attachments) {
		$this->attachments = $attachments;
	}

	public function setDateTime($dateTime) {
		$this->dateTime = $dateTime;
	}

	public function addAttachment(MailAttachmentItem $attachment) {
		$this->attachments[] = $attachment;
	}
	
	public function getMessage() {
		return $this->message;
	}

	public function setMessage($message) {
		$this->message .= $message;
	}

	public function getSubject() {
		return $this->subject;
	}

	public function setSubject($subject) {
		$this->subject .= $subject;
	}
	
	public function hasAttachments() {
		return (count($this->attachments) > 0);
	}

	public function jsonSerialize() {
		return [
			'dateTime' => trim(L10nUtils::formatDateTime($this->dateTime, N2nLocale::getAdmin())),
			'to' => trim($this->to),
			'from' => trim($this->from),
			'cc' => trim($this->cc),
			'bcc' => trim($this->bcc),
			'replyTo' => trim($this->replyTo),
			'attachments' => $this->attachments,
			'message' => trim($this->message),
			'subject' => trim($this->subject)
		];
	}
}
