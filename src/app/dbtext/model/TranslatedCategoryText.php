<?php
namespace dbtext\model;

use dbtext\storage\CategoryData;
use n2n\l10n\N2nLocale;

class TranslatedCategoryText implements CategoryText {
	/**
	 * @var N2nLocale[] $preferedN2nLocales
	 */
	private $preferedN2nLocales;

	/**
	 * @var CategoryText
	 */
	private $decorated;

	/**
	 * @param CategoryText $categoryText
	 * @param array $preferedN2nLocales
	 */
	public function __construct(CategoryText $categoryText, array $preferedN2nLocales) {
		$this->preferedN2nLocales = $preferedN2nLocales;
		$this->decorated = $categoryText;
	}

	/**
	 * @inheritdoc
	 */
	public function t(string $id, array $args, N2nLocale ...$n2nLocale): string {
		return $this->decorated->t($id, $args, $this->preferedN2nLocales);
	}

	/**
	 * @inheritdoc
	 */
	public function tf(string $id, array $args, N2nLocale ...$n2nLocale): string {
		return $this->decorated->tf($id, $args, $this->preferedN2nLocales);
	}
}