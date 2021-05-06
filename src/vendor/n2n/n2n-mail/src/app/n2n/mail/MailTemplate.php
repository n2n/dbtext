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

class MailTemplate {
	private $from;
	private $replyTo;
	private $subject;
	private $message;
	private $attachments = array();
	private $type;
	
	public function __construct($from, $subject, $message) {
		$this->from = $from instanceof MailAddress ? $from : new MailAddress($from);
	}
	
	/**
	 * adds a managed file as an attachment
	 * @param File $file
	 */
	public function addManagable(File $file) {
		$this->addFile($file->getPath(), $file->getOriginalName());
	}
	
	/**
	 * adds a file path as an attachment
	 * @param string $path
	 * @param string $name
	 * @throws MailException
	 */
	public function addFile($path, $name) {
		if (!is_file($path)) throw new MailException('invalid path');
		$this->attachments[$path] = $name;
	}
	
	/**
	 * creates a mail object for a given sender
	 * @param mixed $to
	 * @param array $args
	 */
	public function createMail($to, array $args) {
		$keys = array();
		$values = array();
		foreach ($args as $key => $value) {
			$keys[] = '{' . $key . '}';
			$values[] = $value;
		}
		$subject = str_replace($keys, $values, $this->subject);
		$message = str_replace($keys, $values, $this->message);
		$mail = new Mail($this->from, $subject, $message, $to);
		$mail->setType($this->type);
		foreach ($this->attachments as $path => $name) {
			$mail->addFile($path, $name);
		}
		
		return $mail;
	}
	
	public function setType($type = Mail::TYPE_TEXT) {
		if (!in_array($type, array(Mail::TYPE_TEXT, Mail::TYPE_HTML))) {
			throw new MailException('invalid type: ' . $type);
		}
		$this->type = $type;
	}
}
