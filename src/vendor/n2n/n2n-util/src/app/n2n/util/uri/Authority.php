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
namespace n2n\util\uri;

use n2n\util\type\ArgUtils;

class Authority {
	const USER_PASS_SEPARATOR = ':';
	const USER_INFO_SUFFIX = '@';
	const PORT_PREFIX = ':';

	private $user;
	private $password;
	private $host;
	private $port;

	public function __construct(string $host = null, int $port = null, string $user = null, string $password = null) {
		ArgUtils::assertTrue($user !== null || $password === null);

		$this->user = $user;
		$this->password = $password;
		$this->host = $host;
		$this->port = $port;
	}

	public function getUser() {
		return $this->user;
	}

	public function hasUserInfo() {
		return $this->user !== null;
	}

	public function getPassword() {
		return $this->password;
	}

	public function getHost() {
		return $this->host;
	}

	public function hasHost() {
		return $this->host !== null;
	}

	public function getPort() {
		return $this->port;
	}

	public function hasPort() {
		return $this->port !== null;
	}

	public function isEmpty() {
		return $this->user === null && $this->host === null && $this->port === null;
	}

	public function chHost(string $host = null) {
		if ($this->host === $host) return $this;

		return new Authority($host, $this->port, $this->user, $this->password);
	}

	public function __toString(): string {
		return $this->buildString();
	}

	/**
	 * Converts host to IDNA ASCII form.
	 * @return string
	 */
	public function toIdnaAsciiString() {
		return $this->buildString(false);
	}

	private function buildString($idn = true) {
		$str = '';

		if ($this->user !== null) {
			$str = rawurlencode($this->user);
			if ($this->password !== null) {
				$str .= self::USER_PASS_SEPARATOR . rawurlencode($this->password);
			}
			$str .= self::USER_INFO_SUFFIX;
		}

		if ($idn) {
			$str .= $this->host;
		} else {
			$str .= idn_to_ascii($this->host, null, INTL_IDNA_VARIANT_UTS46);
		}

		if ($this->port !== null) {
			$str .= self::PORT_PREFIX . $this->port;
		}

		return $str;
	}

	public static function create($param) {
		if ($param instanceof Authority) {
			return $param;
		}
		
		ArgUtils::valScalar($param);
		
		$urlParam = '//' . $param;
		
		return new Authority(parse_url($urlParam, PHP_URL_HOST), parse_url($urlParam, PHP_URL_PORT), 
				parse_url($urlParam, PHP_URL_USER));
	}
}
