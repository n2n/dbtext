<?php
namespace dbtext\model;

use dbtext\storage\DbtextCollectionManager;
use n2n\context\RequestScoped;
use n2n\l10n\N2nLocale;
use n2n\core\container\N2nContext;

class DbtextService implements RequestScoped {
	/**
	 * @var DbtextCollection[]
	 */
	private $dbtextCollections;
	/**
	 * @var DbtextCollectionManager $tcm
	 */
	private $tcm;
	/**
	 * 
	 * @var N2nContext
	 */
	private $n2nContext;
	
	private function _init(DbtextCollectionManager $tcm, N2nContext $n2nContext) {
		$this->tcm = $tcm;
		$this->n2nContext = $n2nContext;
	}

	/**
	 * Uses {@see DbtextCollection dbtextCollections} to translate a textblock.
	 * 
	 * @see DbtextCollection::t()
	 * @param string|string[] $ns
	 * @param string $key
	 * @param array|null $args
	 * @param N2nLocale[] ...$n2nLocales
	 * @return string
	 */
	public function t($ns, string $key, array $args = null, N2nLocale ...$n2nLocales): string {
		if (empty($n2nLocales)) {
			$n2nLocales[] = $this->n2nContext->getN2nLocale();
		}

		if (!is_array($ns) && $this->dbtextCollections[$ns]) {
			return $this->dbtextCollections[$ns]->t($key, $args, ...$n2nLocales);
		}
		
		return $this->tc($ns, ...$n2nLocales)->t($key, $args, ...$n2nLocales);
	}

	/**
	 * Uses {@see DbtextCollection dbtextCollections} to translate a textblock.
	 * 
	 * @see DbtextCollection::tf()
	 * @param string|string[] $ns
	 * @param string $key
	 * @param array|null $args
	 * @param N2nLocale[] ...$n2nLocale
	 * @return string
	 */
	public function tf($ns, string $key, array $args = null, N2nLocale ...$n2nLocales): string {
		$namespaces = array();
		if (!is_array($ns)) {
			$namespaces[] = $ns;
		}

		if (!is_array($ns) && $this->dbtextCollections[$ns]) {
			return $this->dbtextCollections[$ns]->tf($key, $args, ...$n2nLocales);
		}
		
		return $this->tc($ns, ...$n2nLocales)->tf($key, $args, ...$n2nLocales);
	}

	/**
	 * Returns fitting {@see TextCollection}
	 *
	 * @param string|string[] $ns
	 * @return DbtextCollection
	 */
	public function tc($ns, N2nLocale ...$n2nLocales): DbtextCollection {
		if (!is_array($ns)) {
			return $this->getOrCreateBasicDbCollection($ns, ...$n2nLocales);
		} elseif (count($ns) === 1) {
			return $this->getOrCreateBasicDbCollection(reset($ns), ...$n2nLocales);
		}
		
		$dbtextCollections = array();
		foreach ($ns as $namespace) {
			$dbtextCollections[] = $this->getOrCreateBasicDbCollection($namespace, ...$n2nLocales);
		}

		return new GroupedDbtextCollection($dbtextCollections, $n2nLocales);
	}

	/**
	 * Clears dbtext cache
	 *
	 * @param string $namespace Clears whole cache if no namespace provided.
	 */
	public function clearCache(string $namespace = null) {
		$this->tcm->clearCache($namespace);
	}

	private function getOrCreateBasicDbCollection(string $namespace, N2nLocale ...$n2nLocale) {
		if (!isset($this->dbtextCollections[$namespace])) {
			$this->dbtextCollections[$namespace] = new BasicDbtextCollection($this->tcm->getGroupData($namespace), ...$n2nLocale);
		}

		return $this->dbtextCollections[$namespace];
	}
}