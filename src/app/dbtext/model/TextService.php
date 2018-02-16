<?php
namespace dbtext\model;

use dbtext\storage\DbTextCollectionManager;
use n2n\context\RequestScoped;
use n2n\core\container\N2nContext;
use n2n\l10n\N2nLocale;

class TextService implements RequestScoped {
	/**
	 * @var DbTextCollection[]
	 */
	private $groupTexts;
	/**
	 * @var DbTextCollectionManager $ctm
	 */
	private $ctm;

	private function _init(DbTextCollectionManager $groupTextManager, N2nContext $n2nContext) {
		$this->ctm = $groupTextManager;
	}

	/**
	 * @see DbTextCollection::t()
	 * @param string $namespace
	 * @param string $id
	 * @param array|null $args
	 * @param N2nLocale[] ...$n2nLocale
	 * @return string
	 */
	public function t(string $namespace, string $id, array $args = null, N2nLocale ...$n2nLocale): string {
		if ($this->groupTexts[$namespace] === null) {
			$this->groupTexts[$namespace] = $this->ct($namespace);
		}

		return $this->groupTexts[$namespace]->t($id, $args, ...$n2nLocale);
	}

	/**
	 * @see DbTextCollection::tf()
	 * @param string $namespace
	 * @param string $id
	 * @param array|null $args
	 * @param N2nLocale[] ...$n2nLocale
	 * @return string
	 */
	public function tf(string $namespace, string $id, array $args = null, N2nLocale ...$n2nLocale): string {
		if ($this->groupTexts[$namespace] === null) {
			$this->groupTexts[$namespace] = $this->ct($namespace);
		}

		return $this->groupTexts[$namespace]->tf($id, $args, ...$n2nLocale);
	}

	/**
	 * Finds fitting {@see TextCollection}
	 *
	 * @param string $namespace
	 * @return TranslatedDbTextCollection
	 */
	public function tc(string $namespace, N2nLocale ...$n2nLocales): DbTextCollection {
		if ($this->groupTexts[$namespace] !== null) {
			return $this->groupTexts[$namespace];
		}

		$groupData = $this->ctm->getGroupData($namespace);

		$this->groupTexts[$namespace] = new BasicDbTextCollection($groupData);
		if (count($n2nLocales) === 0) {
			return $this->groupTexts[$namespace];
		}

		return new TranslatedDbTextCollection($this->groupTexts[$namespace], $n2nLocales);
	}

	/**
	 * If no namespace is provided the whole dbtext cache is removed.
	 * By providing a namespace only data that is saved in namespace is removed.
	 *
	 * @param string $namespace
	 */
	public function clearCache(string $namespace = null) {

	}
}