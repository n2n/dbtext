<?php
namespace dbtext\text;

use dbtext\storage\DbtextCollectionManager;
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
		$ai->p('group', new AnnoManyToOne(Group::getClass(),CascadeType::NONE));
		$ai->p('textTs', new AnnoOneToMany(TextT::getClass(), 'text', CascadeType::ALL, FetchType::LAZY, true));
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
	 * @var Group $group
	 */
	private $group;

	/**
	 * @param string $id
	 * @param TextT[] $textTs
	 */
	public function __construct(string $id = null, Group $group = null, array $textTs = null) {
		$this->id = $id;
		$this->textTs = $textTs;
		$this->group = $group;
	}

	/**
	 * @return string
	 */
	public function getId() {
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
	public function getTextTs() {
		return $this->textTs;
	}

	/**
	 * @param TextT[] $textTs
	 */
	public function setTextTs(\ArrayObject $textTs) {
		$this->textTs = $textTs;
	}

	/**
	 * @return Group
	 */
	public function getGroup() {
		return $this->group;
	}

	/**
	 * @param Group $group
	 */
	public function setGroup(Group $group) {
		$this->group = $group;
	}

	private function _postUpdate(DbtextCollectionManager $dbtextCollectionManager) {
		$dbtextCollectionManager->clearCache($this->group->getNamespace());
	}
}