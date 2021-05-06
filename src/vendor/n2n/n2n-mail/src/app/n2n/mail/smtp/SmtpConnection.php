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

class SmtpConnection {
	/**
	 * @var SmtpConfig
	 */
	private $config;
	private $conn;
	private $timeout;
	
	/**
	 * @param SmtpConfig $config
	 * @param int $timeout
	 */
	public function __construct(SmtpConfig $config, $timeout = 25) {
		$this->config = $config;
		$this->timeout = $timeout;
	}
	
	/**
	 * @throws SmtpConnectionException
	 */
	public function open() {
		$this->conn = fsockopen($this->config->getHost(), $this->config->getPort(), $errno, $errmsg, $this->timeout);
		if (!$this->conn) {
			throw new SmtpConnectionException($errmsg);
		}
		
		// set timeout value for connection
		stream_set_timeout($this->conn, $this->timeout);
		
		// get reply of server
		return $this->getResponse();
	}
	
	/**
	 * @throws SmtpConnectionException
	 */
	public function isOpen() {
		if ($this->conn) {
			$socketStatus = socket_get_status($this->conn);
			if ($socketStatus['eof']) {
				// the socket is valid but we are not connected
				throw new SmtpConnectionException(null, 'n2n_error_mail_eof_caught_while_checking_if_connected');
			}
			return true;
		}
		return false;
	}
	
	public function getTimeout() {
		return $this->timeout;
	}
	
	/**
	 * sends a message to the smtp server
	 * @param string $message
	 * @param bool $getResponse
	 * @param string $eol
	 * @return SmtpResponse
	 */
	public function sendMessage($message, $getResponse = true, $eol = "\r\n") {
		fputs($this->conn, $message . $eol);
		
		if (!$getResponse) return null;
		
		// read answer from server
		return $this->getResponse();
	}
	
	public function close() {
		if (!$this->conn) return;
		fclose($this->conn);
		$this->conn = null;
	}
	
	/**
	 * @throws SmtpConnectionException
	 */
	public function enableCrypto() {
		// start encrypted communication
		if (!@stream_socket_enable_crypto($this->conn, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
			$err = error_get_last();
			throw new SmtpConnectionException($err['message']);			
		}
	}
	
	/**
	 * reads all the lines of the command
	 * 
	 * @return SmtpResponse
	 */
	private function getResponse() {
		$data = '';
		$start = microtime(true); 
		
		// read until end or timeout reached
		while (!feof($this->conn) && (microtime(true) - $start) < ($this->timeout + 1)) {
			$line = fgets($this->conn, 515);
			if ($line === false) break;
			
			$data .= $line;
			
			// smtp has a '-' as 4th character, if not stop reading!
			if (substr($line, 3, 1) == ' ') break;
		}
		return new SmtpResponse($data);
	}
	
	/**
	 * @param string $dataMsg
	 */
	public static function formatMessage($dataMsg) {
		// split the msgData in lines
		$dataMsg = str_replace("\r\n","\n", $dataMsg);
		$dataMsg = str_replace("\r","\n", $dataMsg);
		$lines = explode("\n", $dataMsg);
		
		// check if data starts with header
		$field = substr($lines[0], 0, strpos($lines[0], ':'));
		$inHeaders = false;
		if (!empty($field) && !strstr($field, ' ')) {
			$inHeaders = true;
		}
		
		// the length of a line must not exeed 1000 according to rfc 821
		$maxLineLength = 998;
		
		$linesOut = array();
		
		// make sure, no line is longer than $maxLineLength
		foreach ($lines as $line) {
				
			if ($inHeaders && $line == "") $inHeaders = false;
				
			while (strlen($line) > $maxLineLength) {
				$pos = strrpos(substr($line, 0, $maxLineLength), ' ');
		
				if ($pos) {
					$linesOut[] = substr($line, 0, $pos);
					$line = substr($line, $pos + 1);
				} else {
					// prevent overflow attack -> force a line break
					$pos = $maxLineLength - 1;
					$linesOut[] = substr($line, 0, $pos);
					$line = substr($line, $pos);
				}
		
				if ($inHeaders) $line = "\t" . $line;
			}
			$linesOut[] = $line;
		
		}
		return $linesOut;
	}
}
