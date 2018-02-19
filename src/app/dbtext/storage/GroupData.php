<?php
namespace dbtext\storage;

use n2n\l10n\N2nLocale;
use n2n\reflection\ObjectAdapter;

class GroupData extends ObjectAdapter {
	const STAGE_LOCALE_LANG_ID = 'id';
	const STAGE_LOCALE_ID = 'localeId';
	const STAGE_LOCALE_FALLBACK = 'fallback';

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
		$this->namespace = $namespace;
		$this->data = $data;
	}

	/**
	 * Finds most fitting {@see TextT::$str} for given n2nLocales.
	 * In case no fitting {@see TextT::$str} is found the fallback n2nLocale is used.
	 * If there is not even a fallback {@see TextT::$str} then the  id is returned.
	 *
	 * @param $id
	 * @param N2nLocale[] ...$n2nLocales
	 * @return string
	 */
	public function t(string $id, N2nLocale ...$n2nLocales): string {
		if (!isset($this->data[$id])) {
			return $id;
		}

		array_push($n2nLocales, N2nLocale::getFallback());

		foreach ($n2nLocales as $n2nLocale) {
			$n2nLocaleId = $n2nLocale->getId();
			if (isset($this->data[$id][$n2nLocaleId])) {
				return $this->data[$id][$n2nLocaleId];
			}

			// if no region id than locale id and language id are the same.
			if (null === $n2nLocale->getRegionId()) {
				continue;
			}

			$langId = $n2nLocale->getLanguageId();
			if (isset($this->data[$id][$langId])) {
				return $this->data[$id][$langId];
			}
		}

		return $id;
	}

	/**
	 * This method checks if there is a dataset with given id.
	 *
	 * @param string $id
	 * @return bool
	 */
	public function has(string $id) {
		return isset($this->data[$id]);
	}

	/**
	 * This method adds a dataset with given id
	 *
	 * @param string $id
	 */
	public function add(string $id) {
		$this->data[$id] = array();

		foreach ($this->listeners as $listener) {
			$listener->idAdded($id, $this);
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

	public function getListeners() {
		return $this->listeners;
	}
}