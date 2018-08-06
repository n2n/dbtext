<?php
namespace dbtext\storage;

use n2n\l10n\N2nLocale;
use n2n\reflection\ObjectAdapter;

class GroupData extends ObjectAdapter {
	const PLACEHOLDER_JSON_KEY = 'placeholderJsons';

	/**
	 * @var string $namespace
	 */
	private $namespace;
	/**
	 * @var string[][] $texts
	 */
	private $data;
	/**
	 * @var GroupDataListener[]
	 */
	private $listeners = array();

	/**
	 * @param string $namespace
	 * @param string[][] $data
	 */
	public function __construct($namespace, array $data = array()) {
		if (!isset($data[self::PLACEHOLDER_JSON_KEY])) {
			$data[self::PLACEHOLDER_JSON_KEY] = array();
		}

		$this->namespace = $namespace;
		$this->data = $data;
	}

	/**
	 * Finds {@see TextT::$str} for given n2nLocales.
	 * key returned if no fitting {@see TextT::$str} found.
	 *
	 * @param string $key
	 * @param N2nLocale[] ...$n2nLocales
	 * @return string
	 */
	public function t(string $key, N2nLocale ...$n2nLocales): string {
		if (!isset($this->data[$key])) {
			return $key;
		}

		array_push($n2nLocales, N2nLocale::getFallback());

		foreach ($n2nLocales as $n2nLocale) {
			$n2nLocaleId = $n2nLocale->getId();
			if (isset($this->data[$key][$n2nLocaleId])) {
				return $this->data[$key][$n2nLocaleId];
			}

			// if no region id than locale id and language id are the same.
			if (null === $n2nLocale->getRegionId()) {
				continue;
			}

			$langId = $n2nLocale->getLanguageId();
			if (isset($this->data[$key][$langId])) {
				return $this->data[$key][$langId];
			}
		}

		return $key;
	}

	/**
	 * Use to check key available in {@see Group}.
	 *
	 * @param string $key
	 * @return bool
	 */
	public function has(string $key) {
		return isset($this->data[$key]);
	}

	/**
	 * Method adds key to {@see GroupData::$data} and triggers {@see GroupDataListener::keyAdded() listeners}.
	 *
	 * @param string $key
	 */
	public function add(string $key) {
		$this->data[$key] = array();
		$this->data[self::PLACEHOLDER_JSON_KEY][$key] = '[]';

		foreach ($this->listeners as $listener) {
			$listener->keyAdded($key, $this);
		}
	}

	public function changePlaceholders(string $key, array $placeholders = null) {
		$this->data[self::PLACEHOLDER_JSON_KEY][$key] = $placeholders;

		foreach ($this->listeners as $listener) {
			$listener->placeholdersChanged($key, $this->getNamespace(), $placeholders);
		}
	}

	/**
	 * @param GroupDataListener $listener
	 */
	public function registerListener(GroupDataListener $listener) {
		$this->listeners[] = $listener;
	}

	/**
	 * @param GroupDataListener $listener
	 */
	public function unregisterListener(GroupDataListener $listener) {
		$this->listeners = array_filter($this->listeners, function($a) use($listener) {
			return $a !== $listener;
		});
	}

	/**
	 * @return string
	 */
	public function getNamespace(): string {
		return $this->namespace;
	}

	/**
	 * @param string $namespace
	 */
	public function setNamespace(string $namespace) {
		$this->namespace = $namespace;
	}

	/**
	 * @return string[][]
	 */
	public function getData(): array {
		return $this->data;
	}

	/**
	 * @param string[][] $data
	 */
	public function setData(array $data) {
		$this->data = $data;
	}

	/**
	 * @return GroupDataListener[]
	 */
	public function getListeners() {
		return $this->listeners;
	}

	public function equalsPlaceholders(string $key, array $args = null) {
		if (null === $args && null === $this->data[GroupData::PLACEHOLDER_JSON_KEY][$key]) return true;
		return $args === $this->data[GroupData::PLACEHOLDER_JSON_KEY][$key];
	}
}