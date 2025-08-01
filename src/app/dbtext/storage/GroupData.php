<?php
namespace dbtext\storage;

use n2n\l10n\N2nLocale;
use n2n\reflection\ObjectAdapter;

class GroupData extends ObjectAdapter {
	const TEXTS_KEY = 'texts';
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
	public function __construct(string $namespace, array $data = array()) {
		$this->namespace = $namespace;
		$this->data = $data;

		if (!isset($this->data[self::TEXTS_KEY])) {
			$this->data[self::TEXTS_KEY] = array();
		}

		if (!isset($this->data[self::PLACEHOLDER_JSON_KEY])) {
			$this->data[self::PLACEHOLDER_JSON_KEY] = array();
		}

	}

	/**
	 * Finds {@see TextT::$str} for given n2nLocales.
	 * null returned if no fitting {@see TextT::$str} found.
	 *
	 * @param string $key
	 * @param N2nLocale[] ...$n2nLocales
	 * @return string|null
	 */
	public function find(string $key, N2nLocale ...$n2nLocales): ?string {
		if (!isset($this->data[self::TEXTS_KEY][$key])) {
			return null;
		}

		array_push($n2nLocales, N2nLocale::getFallback());

		foreach ($n2nLocales as $n2nLocale) {
			$n2nLocaleId = $n2nLocale->getId();
			if (isset($this->data[self::TEXTS_KEY][$key][$n2nLocaleId])) {
				return $this->data[self::TEXTS_KEY][$key][$n2nLocaleId];
			}

			// if no region id than locale id and language id are the same.
			if (null === $n2nLocale->getRegionId()) {
				continue;
			}

			$langId = $n2nLocale->getLanguageId();
			if (isset($this->data[self::TEXTS_KEY][$key][$langId])) {
				return $this->data[self::TEXTS_KEY][$key][$langId];
			}
		}

		return null;
	}

	/**
	 * Use to check key available in {@see Group}.
	 *
	 * @param string $key
	 * @return bool
	 */
	public function has(string $key) {
		return isset($this->data[self::TEXTS_KEY][$key]);
	}

	/**
	 * Method adds key to {@see GroupData::$data} and triggers {@see GroupDataListener::keyAdded() listeners}.
	 *
	 * @param string $key
	 */
	public function add(string $key, array $args = []) {
		$this->data[self::TEXTS_KEY][$key] = array();
		$this->data[self::PLACEHOLDER_JSON_KEY][$key] = $args;

		foreach ($this->listeners as $listener) {
			$listener->keyAdded($key, $this, $args);
		}
	}

	public function changePlaceholders(string $key, array $placeholders) {
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
	 * @return string[]
	 */
	public function getKeys() {
		return array_keys($this->data[self::TEXTS_KEY]);
	}

	/**
	 * @return GroupDataListener[]
	 */
	public function getListeners() {
		return $this->listeners;
	}

	/**
	 * @param string $key
	 * @param array $args
	 * @return bool
	 */
	public function equalsPlaceholders(string $key, array $args) {
		if (!isset($this->data[self::PLACEHOLDER_JSON_KEY]) || !isset($this->data[self::PLACEHOLDER_JSON_KEY][$key])) {
			return false;
		}

		return array_keys($args) == array_keys((array) $this->data[self::PLACEHOLDER_JSON_KEY][$key]);
	}

	/**
	 * @throws \OutOfBoundsException
	 * @param string $key
	 * @return string[]
	 */
	public function getPlaceholderNamesOfKey(string $key) {
		if (!$this->has($key)) {
			throw new \OutOfBoundsException('The key "' . $key . '" does not exist');
		}
		return array_keys((array) $this->data[GroupData::PLACEHOLDER_JSON_KEY][$key]);
	}

	function toRecord(): GroupDataRecord {
		return new GroupDataRecord($this->namespace, $this->data);
	}

	static function fromRecord(GroupDataRecord $groupDataRecord): GroupData {
		return new GroupData($groupDataRecord->namespace, $groupDataRecord->data);
	}
}
