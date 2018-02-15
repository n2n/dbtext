<?php
namespace dbtext\model;

use dbtext\storage\CategoryTextManager;
use n2n\context\RequestScoped;
use n2n\core\container\N2nContext;
use n2n\l10n\N2nLocale;

class TextService implements RequestScoped {
	/**
	 * @var CategoryText[]
	 */
	private $categoryTexts;
	/**
	 * @var CategoryTextManager $ctm
	 */
	private $ctm;

	private function _init(CategoryTextManager $categoryTextManager, N2nContext $n2nContext) {
		$this->ctm = $categoryTextManager;
	}

	/**
	 * @see CategoryText::t()
	 * @param string $namespace
	 * @param string $id
	 * @param array|null $args
	 * @param N2nLocale[] ...$n2nLocale
	 * @return string
	 */
	public function t(string $namespace, string $id, array $args = null, N2nLocale ...$n2nLocale): string {
		if ($this->categoryTexts[$namespace] === null) {
			$this->categoryTexts[$namespace] = $this->ct($namespace);
		}

		return $this->categoryTexts[$namespace]->t($id, $args, ...$n2nLocale);
	}

	/**
	 * @see CategoryText::tf()
	 * @param string $namespace
	 * @param string $id
	 * @param array|null $args
	 * @param N2nLocale[] ...$n2nLocale
	 * @return string
	 */
	public function tf(string $namespace, string $id, array $args = null, N2nLocale ...$n2nLocale): string {
		if ($this->categoryTexts[$namespace] === null) {
			$this->categoryTexts[$namespace] = $this->ct($namespace);
		}

		return $this->categoryTexts[$namespace]->tf($id, $args, ...$n2nLocale);
	}

	/**
	 * Finds Translated CategoryText
	 *
	 * @param string $namespace
	 * @return TranslatedCategoryText
	 */
	public function ct(string $namespace, N2nLocale ...$n2nLocales): CategoryText {
		if ($this->categoryTexts[$namespace] !== null) {
			return $this->categoryTexts[$namespace];
		}

		$categoryData = $this->ctm->getCategoryData($namespace);

		$this->categoryTexts[$namespace] = new BasicCategoryText($categoryData);
		if (count($n2nLocales) === 0) {
			return $this->categoryTexts[$namespace];
		}

		return new TranslatedCategoryText($this->categoryTexts[$namespace], $n2nLocales);
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