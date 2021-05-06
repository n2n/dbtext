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

use n2n\log4php\Logger;
use n2n\mail\smtp\SmtpClient;
use n2n\mail\smtp\SmtpConfig;
use n2n\core\N2N;

class Transport {
	public static function send(Mail $mail) {
		if (N2N::getAppConfig()->mail()->isSendingMailEnabled()) {
			$subject = '=?utf-8?B?' . base64_encode($mail->getSubject()) . '?=';
			if (!@mail($mail->getTo(), $subject, $mail->getBody(), $mail->getHeader(true))) {
				$err = error_get_last();
				throw new MailException('Mail could not be sent. Reason: ' . $err['message']);
			}
		}
		
		self::log($mail);
	}
	
	public static function sendOverSmtp(Mail $mail, SmtpConfig $config) {
		$client = new SmtpClient($config);
		$client->connectAndAuthenticate(5);
		self::sendSmtpMail($client, $mail);
		$client->quit();
	}
	
	public static function sendMultipleSmtpMails(array $mails, SmtpConfig $config) {
		$client = new SmtpClient($config);
		$client->connectAndAuthenticate(5);
		foreach ($mails as $mail) {
			self::sendSmtpMail($client, $mail);
		}
		$client->quit();
	}
	
	private static function sendSmtpMail(SmtpClient $client, Mail $mail) {
		$client->sendMail($mail);
		self::log($mail);
	}
	
	private static function log(Mail $mail) {
		$logger = Logger::getLogger('mailLogger');
		$logger->info($mail);
	}

}
