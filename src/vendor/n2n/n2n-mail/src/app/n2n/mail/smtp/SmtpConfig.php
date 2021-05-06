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


use n2n\util\type\ArgUtils;
use n2n\util\StringUtils;

class SmtpConfig {
	const SECURITY_MODE_TLS = 'tls';
	const SECURITY_MODE_SSL = 'ssl';
	
	const PORT_DEFAULT = 25;
	const SECURITY_MODE_DEFAULT = self::SECURITY_MODE_TLS;
	const AUTHENTICATE_DEFAULT = true;
	
	const HOST_SSL_PREFIX = 'ssl://';
	
	private $host;
	private $user;
	private $password;
	private $port = self::PORT_DEFAULT;
	private $authenticate = self::AUTHENTICATE_DEFAULT;
	private $securityMode = self::SECURITY_MODE_DEFAULT;
	private $debugMode = false;
	
	/**
	 * @param string $host
	 * @param string $user
	 * @param string $password
	 * @param int $port
	 * @throws \InvalidArgumentException
	 */
	public function __construct($host, $user = null, $password = null, $port = null, 
			$auth = null, $securityMode = null) {
		$this->host = $host;
		$this->user = $user;
		$this->password = $password;
		if (null !== $port) {
			$this->port = $port;
		}
		if (null !== $auth) {
			$this->authenticate = (bool) $auth;
		}
		if (null !== $securityMode) {
			$this->setSecurityMode($securityMode);
		}
	}
	
	public function getHost() {
		if ($this->securityMode == 'ssl' 
				&& !StringUtils::startsWith(self::HOST_SSL_PREFIX, $this->host)) {
			return self::HOST_SSL_PREFIX . $this->host;
		}
		return $this->host;
	}
	
	public function getUser() {
		return $this->user;
	}
	
	public function getPassword() {
		return $this->password;
	}
	
	public function getPort() {
		return $this->port;
	}
	
	public function setSecurityMode($securityMode) {
		if (null !== $securityMode) {
			ArgUtils::valEnum($securityMode, self::getSecurityModes());
		}
		$this->securityMode = $securityMode;
	}
	
	public function getSecurityMode() {
		return $this->securityMode;
	}
	
	public function setAuthenticate($auth) {
		$this->authenticate = (boolean) $auth;
	}
	
	public function doAuthenticate() {
		return (boolean) $this->authenticate;
	}
	
	public function setDebugMode($mode) {
		$this->debugMode = intval($mode);
	}
	
	public function getDebugMode() {
		return $this->debugMode;
	}
	
	public static function getSecurityModes() {
		return array(self::SECURITY_MODE_SSL, self::SECURITY_MODE_TLS);
	}
	
// 	public static function getPossibleSecurityModes() {
// 		$possibleSecurityModes = array(self::SECURITY_MODE_SSL => self::SECURITY_MODE_SSL, self::SECURITY_MODE_TLS => self::SECURITY_MODE_TLS);
// 		$allTransportations = stream_get_transports();
// 		foreach ($possibleSecurityModes as $key => $value) {
// 			if (in_array($value, $allTransportations)) continue;
// 			unset($possibleSecurityModes[$key]);
// 		}
// 		return $possibleSecurityModes;
// 	}
}
