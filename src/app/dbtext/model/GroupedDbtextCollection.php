<?php
namespace dbtext\model;

use n2n\l10n\N2nLocale;

class GroupedDbtextCollection implements DbtextCollection {
	private $dbtextCollections;
	private $n2nLocales;

	/**
	 * @param DbtextCollection[] $dbtextCollections
	 * @param N2nLocale[] $n2nLocales
	 */
	public function __construct(array $dbtextCollections, array $n2nLocales) {
		$this->dbtextCollections = $dbtextCollections;
		$this->n2nLocales = $n2nLocales;
	}

	/**
	 * Finds most fitting {@see TextT} by locales provided and returns modified {@see TextT::$str}.
	 * {@see TextT::$str} args are replaced by {@see TextCollection::fillArgs()}.
	 *
	 * @param string $key
	 * @param array $args
	 * @param N2nLocale[] ...$n2nLocales
	 * @return string
	 */
	public function t(string $key, array $args = null, N2nLocale ...$n2nLocales): string {
		$passedN2nLocales = $n2nLocales ?? $this->n2nLocales;
		$passedN2nLocales[] = N2nLocale::getFallback();

		if (empty($this->dbtextCollections)) {
			return DbtextService::prettyKey($key, $args);
		}

		$n2nLocales = [];
		foreach ($passedN2nLocales as $passedN2nLocale) {
			$n2nLocales[] = $passedN2nLocale;

			if (null !== $passedN2nLocale->getRegionId()) {
				$n2nLocales[] = new N2nLocale($passedN2nLocale->getLanguageId());
			}
		}

		foreach ($this->dbtextCollections as $dbtextCollection) {
			if ($dbtextCollection->has($key)) {
				return $dbtextCollection->t($key, $args, ...$n2nLocales);
			}
		}

		if (reset($this->dbtextCollections)) {
			return reset($this->dbtextCollections)->t($key);
		}

		return DbtextService::prettyKey($key, $args);
	}

	/**
	 * Finds most fitting {@see TextT} by locales provided and returns modified {@see TextT::$str}.
	 * {@see TextT::$str} args are replaced by the printf method.
	 *
	 * @param string $key
	 * @param array $args
	 * @param N2nLocale[] ...$n2nLocales
	 * @return string
	 */
	public function tf(string $key, array $args = null, N2nLocale ...$n2nLocales): string {
		foreach ($this->dbtextCollections as $dbtextCollection) {
			if ($dbtextCollection->has($key)) {
				return $dbtextCollection->tf($key, $args, ...$this->n2nLocales);
			}
		}

		if (reset($this->dbtextCollections)) {
			return reset($this->dbtextCollections)->t($key);
		}

		return DbtextService::prettyKey($key, $args);
	}

	/**
	 * {@inheritDoc}
	 * @see \dbtext\model\DbtextCollection::has()
	 */
	public function has(string $key): bool {
		foreach ($this->dbtextCollections as $dbtextCollection) {
			if ($dbtextCollection->has($key)) {
				return true;
			}
		}

		return false;
	}

	public function getPlaceholderNamesOfKey(string $key): array {
		foreach ($this->dbtextCollections as $dbtextCollection) {
			if ($dbtextCollection->has($key)) {
				return $dbtextCollection->getPlaceholderNamesOfKey($key);
			}
		}

		return array();
	}

	/**
	 * {@inheritDoc}
	 * @see \dbtext\model\DbtextCollection::getKeys()
	 */
	public function getKeys(): array {
		if (1 === count($this->dbtextCollections)) {
			return reset($this->dbtextCollections)->getKeys();
		}

		$keys = array();

		foreach ($this->dbtextCollections as $dbtextCollection) {
			array_push($keys, ...$dbtextCollection->getKeys());
		}

		return array_unique($keys);
	}
}
