<?php
namespace dbtext\text;

use n2n\l10n\N2nLocale;
use n2n\persistence\orm\annotation\AnnoManyToOne;
use n2n\persistence\orm\annotation\AnnoTable;
use n2n\persistence\orm\CascadeType;
use n2n\reflection\annotation\AnnoInit;
use n2n\reflection\ObjectAdapter;

class TextT extends ObjectAdapter {
	private static function _annos(AnnoInit $ai) {
		$ai->c(new AnnoTable('dbtext_text_text_t'));
		$ai->p('text', new AnnoManyToOne(Text::getClass(), CascadeType::NONE));
	}

	/**
	 * @param int $id
	 * @param N2nLocale $n2nLocale
	 * @param string $str
	 * @param Text $text
	 */
	public function __construct($id, N2nLocale $n2nLocale, $str, Text $text) {
		$this->id = $id;
		$this->n2nLocale = $n2nLocale;
		$this->str = $str;
		$this->text = $text;
	}

	/**
	 * @var int $id
	 */
	private $id;
	/**
	 * @var N2nLocale $n2nLocale
	 */
	private $n2nLocale;
	/**
	 * @var string $str
	 */
	private $str;
	/**
	 * @var Text $text
	 */
	private $text;

	/**
	 * @return int
	 */
	public function getId(): int {
		return $this->id;
	}

	/**
	 * @param int $id
	 */
	public function setId(int $id) {
		$this->id = $id;
	}

	/**
	 * @return N2nLocale
	 */
	public function getN2nLocale(): N2nLocale {
		return $this->n2nLocale;
	}

	/**
	 * @param N2nLocale $n2nLocale
	 */
	public function setN2nLocale(N2nLocale $n2nLocale) {
		$this->n2nLocale = $n2nLocale;
	}

	/**
	 * @return Text
	 */
	public function getText(): Text {
		return $this->text;
	}

	/**
	 * @param Text $text
	 */
	public function setText(Text $text) {
		$this->text = $text;
	}

	/**
	 * @return string
	 */
	public function getStr(): string {
		return $this->str;
	}

	/**
	 * @param string $str
	 */
	public function setStr(string $str) {
		$this->str = $str;
	}

	private function _postUpdate(CategoryTextManager $categoryTextManager) {
		$categoryTextManager->clearCache($this->text->getCategory()->getNamespace());
	}
}