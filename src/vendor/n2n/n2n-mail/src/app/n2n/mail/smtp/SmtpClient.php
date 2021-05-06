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
namespace n2n\mail\smtp;

use n2n\mail\MailAddress;
use n2n\mail\Mail;

class SmtpClient {
	/**
	 * @var SmtpConfig
	 */
	private $config;
	/**
	 * @var SmtpConnection
	 */
	private $connection;
	private $useVerp = false;
	private $ehlo;
	
	const EOL = "\r\n";
	
	/**
	 * @param SmtpConfig $config
	 */
	public function __construct(SmtpConfig $config, $timeout = 25) {
		$this->config = $config;
		$this->connection = new SmtpConnection($config, $timeout);
	}
	
	/**
	 * connects to an smtp server
	 * 
	 * @throws SmtpException
	 * @throws SmtpConnectionException
	 */
	public function connect() {
		// only continue, if not connected
		if ($this->isConnected()) throw new SmtpException('SMTP client already connected');
		
		$this->log('connecting...');
		$response = $this->connection->open();
		$this->log($response);		
	}
	
	/**
	 * returns whether or not a connection is open
	 * 
	 * @return boolean
	 */
	function isConnected() {
		try {
			return $this->connection->isOpen();
		} catch (SmtpException $ex) {
			$this->log('EOF caught while checking if connected', 'client');
			$this->close();
		}
		return false;
	}
	
	/**
	 * starts tls encryption
	 * 
	 * @throws SmtpConnectionException
	 * @throws SmtpException
	 */
	public function startTls() {
		$this->sendMessage('STARTTLS', 220, 'SMTP STARTTLS not accepted from server');
		$this->connection->enableCrypto();
	}
	
	/**
	 * sends authentification to the server
	 * 
	 * @throws SmtpConnectionException
	 */
	public function authenticate() {
		$this->sendMessage('AUTH LOGIN', 334, 'SMTP AUTH not accepted from server');
		$this->sendMessage($this->config->getUser(), 334, 'SMTP username not accepted from server', true);
		$this->sendMessage($this->config->getPassword(), 235, 'SMTP password not accepted from server', true);
	}
	
	/**
	 * starts the send mail process. after mail() you should call recipient() 
	 * 
	 * @param string $fromEmail
	 * @throws SmtpConnectionException
	 */
	public function mail($fromEmail) {
		if (!filter_var($fromEmail, FILTER_VALIDATE_EMAIL)) {
			throw new SmtpException('smtp mail must be a valid email');
		}
		$verp = $this->useVerp ? ' XVERP' : '';
		$this->sendMessage("MAIL FROM:<{$fromEmail}>{$verp}", 250, 'SMTP MAIL not accepted from server');
	}
	
	/**
	 * second step in the send mail process. call recipient() for each recipient of the mail
	 * 
	 * @param string $to
	 * @throws SmtpConnectionException
	 */
	public function recipient($toEmail) {
		if (!filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
			throw new SmtpException('smtp recipient must be a valid email');
		}
		$this->sendMessage("RCPT TO:<{$toEmail}>", array(250, 251), 'RCPT not accepted from server');
	}
	
	/**
	 * 
	 * @param string $dataMsg
	 * @throws SmtpConnectionException
	 */
	public function data($dataMsg) {
		// start data connection
		$this->sendMessage('DATA', 354, 'SMTP DATA not accepted from server');
		
		// get lines
		$lineOuts = SmtpConnection::formatMessage($dataMsg);
		foreach ($lineOuts as $lineOut) {
			// prevent sending an unwanted end
			if (strlen($lineOut) > 0 && substr($lineOut, 0, 1) == '.') {
				$lineOut = '.' . $lineOut;
			}
			$this->sendMessage($lineOut);
		}
		
		// end data sending
		$this->sendMessage('.', 250, 'SMTP . not accepted from server');
	}
	
	/**
	 * resets currently running transactions
	 * 
	 * @throws SmtpConnectionException
	 */
	public function reset() {
		$this->sendMessage('RSET', 250, 'SMTP reset failed');
	}
	
	/**
	 * quits the communication with the server. is private as you shoul always call quit
	 * 
	 * @param bool $closeOnError
	 * @throws SmtpConnectionException
	 */
	public function quit($closeOnError = true) {
		try {
			$this->sendMessage('QUIT', 221, 'SMTP server rejected quit command');
		} catch (SmtpConnectionException $ex) {
			if (!$closeOnError) throw $ex;
		}
		$this->close();
	}
	
	/**
	 * closes the connection and resets $this->ehlo
	 * method is private as this funtion should only be called from quit method
	 */
	private function close() {
		$this->ehlo = null;
		if ($this->connection->isOpen()) {
			$this->connection->close();
			$this->log('ehlo reseted, connection closed', false);
		}
	}
	
	/**
	 * method to call connect, hello, tls, hello and authenticate if needed
	 */
	public function connectAndAuthenticate() {
		// 1. connect
		if (!$this->isConnected()) {
			$this->connect();
		}
		$this->hello();
		
		// 2. open tls connection if needed
		$securityMode = $this->config->getSecurityMode();
		if ($securityMode == SmtpConfig::SECURITY_MODE_TLS) {
			$this->startTls();
			$this->hello();
		}
		
		// 3. authenticat if needed
		if ($this->config->doAuthenticate()) {
			$this->authenticate();
		}
	}
	
	/**
	 * method to call mail, recipients and data after each other
	 * 
	 * @param Mail $mail
	 */
	public function sendMail(Mail $mail) {
		$sender = $mail->getSender();
		if (!$sender instanceof MailAddress) {
			throw new SmtpException('no sender found on mail');
		}
		$this->mail($sender->getEmail());
		
		foreach ($mail->getRecipients() as $address) {
			$address instanceof MailAddress;
			// @todo: catch errors
			$this->recipient($address->getEmail());
		}
		
		$this->data($mail->getData());
	}
	
	/**
	 * sends EHLO (or HELO) to the server
	 */
	public function hello() {
		try {
			$this->sendHello('EHLO ' . $this->config->getHost());
		} catch (SmtpConnectionException $ex) {
			$this->log($ex->getMessage(), 'client');
			$this->sendHello('HELO ' . $this->config->getHost());
		}
	}
	
	/**
	 * @param string $hello
	 */
	private function sendHello($hello) {
		$response = $this->sendMessage($hello, 250, 'SMTP ' . $hello . ' not accepted from server');
		$this->ehlo = $response->getData();
	}
	
	/**
	 * sends a message over the SmtpConnection to the server
	 * @param string $message
	 * @param mixed $successCodes
	 * @param string $errorMsg
	 * @param bool $baseEncode
	 * @throws SmtpConnectionException
	 * @return SmtpResponse
	 */
	private function sendMessage($message, $successCodes = null, $errorMsg = null, $baseEncode = false) {
		$this->log($message);
		$getResponse = (bool) $successCodes;
		if ($baseEncode) $message = base64_encode($message);
		try {
			$response = $this->connection->sendMessage($message, $getResponse, self::EOL);
		} catch (SmtpConnectionException $ex) {
			$this->log($ex->getMessage(), 'server', true);
			throw $ex;
		}
		if (!$response) return null;
		
		$successCodes = is_array($successCodes) ? $successCodes : array($successCodes);
		if (!in_array($response->getCode(), $successCodes)) {
			$this->log($response, 'server', 1);
			throw new SmtpConnectionException($errorMsg);
		}
		$this->log($response);
		
		return $response;
	}
	
	/**
	 * makes a log entry
	 * @param string $message
	 * @param string $party 
	 * @param bool $exception
	 */
	private function log($message, $party = null, $exception = false) {
		if (!$this->config->getDebugMode()) return;
		
		if (!$party) {
			$party = $message instanceof SmtpResponse ? 'server' : 'client';
		}
		// @todo: add logger possibility
		$eol = self::EOL;
		echo "SMTP " . strtoupper($party) . ":{$eol}{$message}{$eol}";
		
	}
}
