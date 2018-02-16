<?php
namespace dbtext\text;

use dbtext\storage\DbTextCollectionManager;
use n2n\persistence\orm\annotation\AnnoId;
use n2n\persistence\orm\annotation\AnnoOneToMany;
use n2n\persistence\orm\CascadeType;
use n2n\reflection\annotation\AnnoInit;
use n2n\reflection\ObjectAdapter;
use n2n\persistence\orm\annotation\AnnoTable;

/**
 * Represents the namespace texts belong to.
 * @package dbtext\text
 */
class Group extends ObjectAdapter {
	private static function _annos(AnnoInit $ai) {
		$ai->c(new AnnoTable('dbtext_text_group'));
		$ai->p('namespace', new AnnoId(false));
		$ai->p('texts', new AnnoOneToMany(Text::getClass(), 'group', CascadeType::ALL));
	}

	/**
	 * @var string $namespace
	 */
	private $namespace;
	/**
	 * @var string $label
	 */
	private $label;
	/**
	 * @var Text[] $texts
	 */
	private $texts;

	/**
	 * @param string $namespace
	 */
	public function __construct($namespace = null) {
		$this->namespace = $namespace;
	}

	/**
	 * @return string
	 */
	public function getNamespace() {
		return $this->namespace;
	}

	/**
	 * @param string $namespace
	 */
	public function setNamespace($namespace) {
		$this->namespace = $namespace;
	}

	/**
	 * @return string
	 */
	public function getLabel() {
		return $this->label;
	}

	/**
	 * @param string $label
	 */
	public function setLabel($label) {
		$this->label = $label;
	}

	/**
	 * @return Text[]
	 */
	public function getTexts() {
		return $this->texts;
	}

	/**
	 * @param Text[] $texts
	 */
	public function setTexts($texts) {
		$this->texts = $texts;
	}

	private function _postUpdate(DbTextCollectionManager $groupTextManager) {
		$groupTextManager->clearCache($this->namespace);
	}
}