<?php
namespace dbtext\text;

use n2n\persistence\orm\annotation\AnnoId;
use n2n\persistence\orm\annotation\AnnoManyToOne;
use n2n\persistence\orm\annotation\AnnoOneToMany;
use n2n\persistence\orm\annotation\AnnoTable;
use n2n\persistence\orm\CascadeType;
use n2n\persistence\orm\FetchType;
use n2n\reflection\annotation\AnnoInit;
use n2n\reflection\ObjectAdapter;

/**
 * Text holds Translations {@see TextT}.
 * @package dbtext\text
 */
class Text extends ObjectAdapter {
	private static function _annos(AnnoInit $ai) {
		$ai->c(new AnnoTable('dbtext_text_text'));
		$ai->p('id', new AnnoId(false));
		$ai->p('category', new AnnoManyToOne(Category::getClass(),CascadeType::NONE));
		$ai->p('textTs', new AnnoOneToMany(TextT::getClass(), 'text', CascadeType::ALL, FetchType::LAZY));
	}

	/**
	 * @var string $id
	 */
	private $id;
	/**
	 * @var TextT[] $textTs
	 */
	private $textTs;
	/**
	 * @var Category $category
	 */
	private $category;

	/**
	 * Text constructor.
	 * @param string $id
	 * @param TextT[] $textTs
	 */
	public function __construct(string $id = null, Category $category = null, array $textTs = null) {
		$this->id = $id;
		$this->textTs = $textTs;
		$this->category = $category;
	}

	/**
	 * @return string
	 */
	public function getId(): string {
		return $this->id;
	}

	/**
	 * @param string $id
	 */
	public function setId(string $id) {
		$this->id = $id;
	}

	/**
	 * @return TextT[]
	 */
	public function getTextTs(): array {
		return $this->textTs;
	}

	/**
	 * @param TextT[] $textTs
	 */
	public function setTextTs(array $textTs) {
		$this->textTs = $textTs;
	}

	/**
	 * @return Category
	 */
	public function getCategory(): Category {
		return $this->category;
	}

	/**
	 * @param Category $category
	 */
	public function setCategory(Category $category) {
		$this->category = $category;
	}

	private function _postUpdate(CategoryTextManager $categoryTextManager) {
		$categoryTextManager->clearCache($this->category->getNamespace());
	}
}