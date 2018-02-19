<?php
namespace dbtext\model;

use dbtext\storage\DbtextCollectionManager;
use n2n\context\RequestScoped;
use n2n\l10n\N2nLocale;

class TextService implements RequestScoped {
	/**
	 * @var DbtextCollection[]
	 */
	private $dbtextCollections;
	/**
	 * @var DbtextCollectionManager $tcm
	 */
	private $tcm;

	private function _init(DbtextCollectionManager $tcm) {
		$this->tcm = $tcm;
	}

	/**
	 * @see DbtextCollection::t()
	 * @param string $namespace
	 * @param string $id
	 * @param array|null $args
	 * @param N2nLocale[] ...$n2nLocale
	 * @return string
	 */
	public function t(string $namespace, string $id, array $args = null, N2nLocale ...$n2nLocale): string {
		if (!isset($this->dbtextCollections[$namespace])) {
			$this->dbtextCollections[$namespace] = $this->tc($namespace);
		}

		return $this->dbtextCollections[$namespace]->t($id, $args, ...$n2nLocale);
	}

	/**
	 * @see DbtextCollection::tf()
	 * @param string $namespace
	 * @param string $id
	 * @param array|null $args
	 * @param N2nLocale[] ...$n2nLocale
	 * @return string
	 */
	public function tf(string $namespace, string $id, array $args = null, N2nLocale ...$n2nLocale): string {
		if (!isset($this->dbtextCollections[$namespace]) && count($n2nLocale) === 0) {
			$this->dbtextCollections[$namespace] = $this->tc($namespace);
		}

		return $this->dbtextCollections[$namespace]->tf($id, $args, ...$n2nLocale);
	}

	/**
	 * Finds fitting {@see TextCollection}
	 *
	 * @param string $namespace
	 * @return DbtextCollection
	 */
	public function tc(string $namespace, N2nLocale ...$n2nLocales): DbtextCollection {
		if (!isset($this->dbtextCollections[$namespace])) {
			$this->dbtextCollections[$namespace] = new BasicDbtextCollection($this->tcm->getGroupData($namespace));
		}

		if (empty($n2nLocales)) {
			return $this->dbtextCollections[$namespace];
		}

		return new TranslatedDbtextCollection($this->dbtextCollections[$namespace], $n2nLocales);
	}

	/**
	 * If no namespace is provided the whole dbtext cache is removed.
	 * By providing a namespace only data that is saved in namespace is removed.
	 *
	 * @param string $namespace
	 */
	public function clearCache(string $namespace = null) {
		$this->tcm->clearCache($namespace);
	}
}