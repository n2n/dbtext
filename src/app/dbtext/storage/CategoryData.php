<?php
namespace dbtext\storage;

use n2n\core\N2N;
use n2n\l10n\N2nLocale;
use n2n\reflection\ObjectAdapter;
use n2n\util\ex\NotYetImplementedException;

class CategoryData extends ObjectAdapter {
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
	 * @var CategoryDataListener[]
	 */
	private $listeners = array();

	/**
	 * CategoryData constructor.
	 * @param string $namespace
	 * @param \string[][] $data
	 */
	public function __construct($namespace, array $data = null) {
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
			$this->add($id);
		}

		$dataN2nLocales = array();
		$stages = array(self::STAGE_LOCALE_ID, self::STAGE_LOCALE_LANG_ID, self::STAGE_LOCALE_FALLBACK);
		foreach ($stages as $stage) {
			foreach ($n2nLocales as $n2nLocale) {
				foreach ($this->data[$id] as $n2nLocaleId => $data) {
					if (!isset($dataN2nLocales[$n2nLocaleId])) {
						$dataN2nLocales[$n2nLocaleId] = N2nLocale::build($n2nLocaleId);
					}

					$dataN2nLocale = $dataN2nLocales[$n2nLocaleId];
					switch ($stage) {
						case self::STAGE_LOCALE_ID:
							if ($dataN2nLocale->getId() === $n2nLocale->getId()) {
								return $this->data[$id][$n2nLocaleId];
							}
							break;
						case self::STAGE_LOCALE_LANG_ID:
							if ($dataN2nLocale->getLanguageId() === $n2nLocale->getLanguageId()) {
								return $this->data[$id][$n2nLocaleId];
							}
							break;
						case self::STAGE_LOCALE_FALLBACK:
							if ($dataN2nLocale->getLanguageId() === N2nLocale::getFallback()->getLanguageId()) {
								return $this->data[$id][$n2nLocaleId];
							}
							break;
					}
				}
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
	public function has(string $id): boolean {
		throw new NotYetImplementedException('categoryData -> has()');
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
	 * @param CategoryDataListener $listener
	 */
	public function registerListener(CategoryDataListener $listener) {
		$this->listeners[] = $listener;
	}

	/**
	 * @param CategoryDataListener $listener
	 */
	public function unregisterListener(CategoryDataListener $listener) {
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
	 * @return \string[][]
	 */
	public function getData(): array {
		return $this->data;
	}

	/**
	 * @param \string[][] $data
	 */
	public function setData(array $data) {
		$this->data = $data;
	}
}