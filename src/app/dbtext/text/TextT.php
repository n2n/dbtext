<?php
namespace dbtext\text;

use dbtext\storage\DbtextCollectionManager;
use n2n\l10n\N2nLocale;
use n2n\persistence\orm\annotation\AnnoManyToOne;
use n2n\persistence\orm\annotation\AnnoTable;
use n2n\reflection\ObjectAdapter;
use n2n\reflection\annotation\AnnoInit;
use rocket\impl\ei\component\prop\translation\Translatable;

class TextT extends ObjectAdapter implements Translatable {
	private static function _annos(AnnoInit $ai) {
		$ai->c(new AnnoTable('dbtext_text_t'));
		$ai->p('text', new AnnoManyToOne(Text::getClass()));
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
	 * @param int $id
	 * @param N2nLocale $n2nLocale
	 * @param string $str
	 * @param Text $text
	 */
	public function __construct(int $id = null, N2nLocale $n2nLocale = null, string $str = null, Text $text = null) {
		$this->id = $id;
		$this->n2nLocale = $n2nLocale;
		$this->str = $str;
		$this->text = $text;
	}

	private function _postUpdate(DbtextCollectionManager $dbtextCollectionManager) {
		$dbtextCollectionManager->clearCache($this->text->getGroup()->getNamespace());
	}

	public function getId() {
		return $this->id;
	}
	
	public function setId(int $id) {
		$this->id = $id;
	}
	
	/**
	 * @return N2nLocale
	 */
	public function getN2nLocale() {
		return $this->n2nLocale;
	}

	/**
	 * @param N2nLocale $n2nLocale
	 */
	public function setN2nLocale(N2nLocale $n2nLocale) {
		$this->n2nLocale = $n2nLocale;
	}

	public function getStr() {
		return $this->str;
	}

	public function setStr($str) {
		$this->str = $str;
	}

	/**
	 * @return Text
	 */
	public function getText() {
		return $this->text;
	}

	/**
	 * @param Text $text
	 */
	public function setText(Text $text) {
		$this->text = $text;
	}
}