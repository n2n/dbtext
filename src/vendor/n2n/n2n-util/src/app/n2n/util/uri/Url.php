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

class Url {
	const SCHEME_SEPARATOR = ':';
	const AUTHORITY_PREFIX = '//';
	const PATH_PREFIX = Path::DELIMITER;
	const QUERY_PREFIX = '?';
	const FRAGMENT_PREFIX = '#';

	protected $scheme;
	protected $userInfo;
	protected $authority;
	protected $path;
	protected $query;
	protected $fragment;

	public function __construct(string $scheme = null, Authority $authority = null, Path $path = null,
			Query $query = null, string $fragment = null) {
		$this->scheme = ArgUtils::stringOrNull($scheme);
		$this->authority = $authority;
		$this->path = $path;
		$this->query = $query;
		$this->fragment = $fragment;
	}
	/**
	 * @return string
	 */
	public function getScheme() {
		return $this->scheme;
	}

	public function hasScheme() {
		return null !== $this->scheme;
	}
	/**
	 * @return Authority
	 */
	public function getAuthority() {
		if ($this->authority === null) {
			return new Authority();
		}

		return $this->authority;
	}
	/**
	 * @return Path
	 */
	public function getPath() {
		if ($this->path === null) {
			return new Path(array());
		}
		return $this->path;
	}
	/**
	 * @return Query
	 */
	public function getQuery() {
		if ($this->query === null) {
			return new Query(array());
		}
		return $this->query;
	}
	/**
	 * @return string|null
	 */
	public function getFragment() {
		return $this->fragment;
	}
	/**
	 * @param string $scheme
	 * @return \n2n\util\uri\Url
	 */
	public function chScheme(string $scheme = null) {
		if ($scheme === $this->scheme) return $this;
		return new Url($scheme, $this->authority, $this->path, $this->query, $this->fragment);
	}
	/**
	 * @param mixed $authority
	 * @return \n2n\util\uri\Url
	 */
	public function chAuthority($authority) {
		if ($authority === $this->authority) return $this;
		return new Url($this->scheme, Authority::create($authority), $this->path, $this->query, $this->fragment);
	}
	/**
	 * @param mixed $userInfo
	 * @return \n2n\util\uri\Url
	 */
	public function chUserInfo($userInfo) {
		if ($this->getAuthority()->getUserInfo() === $userInfo) return $this;
		return new Url($this->scheme, $this->getAuthority()->chUserInfo($userInfo), $this->path, $this->query, $this->fragment);
	}
	/**
	 * @param mixed $host
	 * @return \n2n\util\uri\Url
	 */
	public function chHost(string $host = null) {
		if ($this->getAuthority()->getHost() === $host) return $this;
		return new Url($this->scheme, $this->getAuthority()->chHost($host), $this->path, $this->query, $this->fragment);
	}
	/**
	 * @param mixed $port
	 * @return \n2n\util\uri\Url
	 */
	public function chPort(int $port = null) {
		if ($this->getAuthority()->getPort() === $port) return $this;
		return new Url($this->scheme, $this->getAuthority()->chPort($port), $this->path, $this->query, $this->fragment);
	}
	/**
	 * @param mixed $path
	 * @return \n2n\util\uri\Url
	 */
	public function chPath(string $path = null) {
		if ($path === $this->path) return $this;
		return new Url($this->scheme, $this->authority, Path::create($path), $this->query, $this->fragment);
	}
	/**
	 * @param mixed $query
	 * @return \n2n\util\uri\Url
	 */
	public function chQuery($query) {
		if ($query === $this->query) return $this;
		return new Url($this->scheme, $this->authority, $this->path, Query::create($query), $this->fragment);
	}
	/**
	 * @param string $fragment
	 * @return \n2n\util\uri\Url
	 */
	public function chFragment($fragment) {
		if ($fragment === $this->fragment) return $this;
		return new Url($this->scheme, $this->authority, $this->path, $this->query, $fragment);
	}

	public function ext($relativeUrl) {
		$relativeUrl = Url::build($relativeUrl);
		
		if ($relativeUrl === null) return $this;
		
		if (!$relativeUrl->isRelative()) {
			throw new \InvalidArgumentException('Passed url is not relative: ' . $relativeUrl);
		}

		return $this->extR($relativeUrl->getPath(), $relativeUrl->getQuery(), $relativeUrl->getFragment());
	}
	/**
	 * @param mixed $pathExtEnc
	 * @param mixed $query
	 * @param mixed $fragment
	 * @return \n2n\util\uri\Url
	 */
	public function extR($pathExt = null, $queryExt = null, $fragment = null) {
		if ($pathExt === null && $queryExt === null && $fragment === null) return $this;

		return new Url($this->scheme, $this->authority, $this->getPath()->ext($pathExt), $this->getQuery()->ext($queryExt),
			($fragment === null ? $this->fragment : $fragment));
	}

	/**
	 * @param mixed ...$pathPartExts
	 * @return \n2n\util\uri\Url
	 */
	public function pathExt(...$pathPartExts) {
		return new Url($this->scheme, $this->authority, $this->getPath()->ext(...$pathPartExts), $this->query, $this->fragment);
	}

	/**
	 * @param mixed ...$pathExts
	 * @return \n2n\util\uri\Url
	 */
	public function pathExtEnc(...$pathExts) {
		return new Url($this->scheme, $this->authority, $this->getPath()->extEnc(...$pathExts), $this->query, $this->fragment);
	}

	/**
	 * @param mixed $query
	 * @return \n2n\util\uri\Url
	 */
	public function queryExt($query) {
		return new Url($this->scheme, $this->authority, $this->getPath(), $this->getQuery()->ext($query), $this->fragment);
	}

	/**
	 * @param number $num
	 * @return \n2n\util\uri\Url
	 */
	public function reducedPath($num = 1) {
		return new Url($this->scheme, $this->authority, $this->getPath()->reduced($num), $this->query, $this->fragment);
	}
	/**
	 * @param number $start
	 * @param string $num
	 * @return \n2n\util\uri\Url
	 */
	public function subPath($start, $num = null) {
		return new Url($this->scheme, $this->authority, $this->getPath()->sub($start, $num), $this->query, $this->fragment);
	}
	/**
	 * @return \n2n\util\uri\Url
	 */
	public function toRelativeUrl() {
		return new Url(null, null, $this->path, $this->query, $this->fragment);
	}

	public static function createRelativeUrl($path = null, $query = null, $fragment = null) {
		return new Url(null, null, Path::create($path), Query::create($query), $fragment);
	}

	/**
	 * @param $expression
	 * @return Url|null
	 */
	public static function build($expression, bool $lenient = false) {
		if ($expression === null || $expression instanceof Url) return $expression;

		return self::create($expression, $lenient);
	}

	/**
	 * @param mixed $expression
	 * @throws \InvalidArgumentException
	 * @return \n2n\util\uri\Url
	 */
	public static function create($expression, bool $lenient = false) {
		if ($expression instanceof Url) {
			return $expression;
		}

		if ($expression instanceof Authority) {
			return new Url(null, $expression);
		}

		if ($expression instanceof Path) {
			return new Url(null, null, $expression);
		}

		if (is_array($expression)) {
			return new Url(null, null, Path::create($expression));
		}

		if ($expression instanceof Query) {
			return new Url(null, null, null, $expression);
		}

		$uriMap = parse_url((string) $expression);
		if ($uriMap === null) {
			throw new \InvalidArgumentException('Invalid uri: ' . $expression);
		}

		$uri = new Url();
		if (isset($uriMap['scheme'])) {
			$uri->scheme = $uriMap['scheme'];
		}
		if (isset($uriMap['host']) || isset($uriMap['user'])) {
			$uri->authority = new Authority($uriMap['host'] ?? null, $uriMap['port'] ?? null, $uriMap['user'] ?? null,
					$uriMap['pass'] ?? null);
		}
		if (isset($uriMap['path'])) {
			$uri->path = Path::create($uriMap['path'], $lenient);
		}
		if (isset($uriMap['query'])) {
			$uri->query = Query::create($uriMap['query']);
		}
		if (isset($uriMap['fragment'])) {
			$uri->fragment = rawurldecode($uriMap['fragment']);
		}
		return $uri;
	}

	/**
	 * @return string
	 */
	public function __toString(): string {
		return $this->buildString();
	}

	public function isRelative() {
		return $this->scheme === null && ($this->authority === null || $this->authority->isEmpty());
	}

	/**
	 * Converts host name to IDNA ASCII form.
	 * @return string
	 */
	public function toIdnaAsciiString() {
		return $this->buildString(false);
	}

	private function buildString(bool $idn = true) {
		$str = '';

		$leadingPathDelimiter = null;

		if ($this->scheme !== null) {
			$str .= $this->scheme . self::SCHEME_SEPARATOR;
		}

		if ($this->authority !== null && !$this->authority->isEmpty()) {
			$str .= self::AUTHORITY_PREFIX . ($idn ? $this->authority : $this->authority->toIdnaAsciiString());
			$leadingPathDelimiter = $this->path !== null && !$this->path->isEmpty();
		}

		if ($this->path !== null) {
			$str .= $this->path->toRealString($leadingPathDelimiter);
		}

		if ($this->query !== null && !$this->query->isEmpty()) {
			$str .= self::QUERY_PREFIX . $this->query;
		}

		if ($this->fragment !== null) {
			$str .= self::FRAGMENT_PREFIX . rawurlencode($this->fragment);
		}

		return $str;
	}

	/**
	 * @param $url
	 * @return bool
	 */
	public function equals($url): bool {
		return $url instanceof Url && (string) $this  === (string) $url;
	}
}
