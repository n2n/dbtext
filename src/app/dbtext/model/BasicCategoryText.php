<?php
namespace dbtext\model;

use dbtext\DbTextUtils;
use dbtext\storage\CategoryData;
use n2n\l10n\N2nLocale;
use n2n\l10n\TextCollection;

class BasicCategoryText implements CategoryText {
	/**
	 * @var CategoryData
	 */
	private $categoryData;

	/**
	 * @param CategoryData $categoryData
	 */
	public function __construct(CategoryData $categoryData) {
		$this->categoryData = $categoryData;
	}

	/**
	 * @inheritdoc
	 */
	public function t(string $id, array $args = null, N2nLocale ...$n2nLocale): string {
		return TextCollection::fillArgs($this->categoryData->t($id, ...$n2nLocale), $args);
	}

	/**
	 * @inheritdoc
	 */
	public function tf(string $id, array $args = null, N2nLocale ...$n2nLocale): string {
		return DbTextUtils::processTextF($this->categoryData->t($id, ...$n2nLocale), $args);
	}
}