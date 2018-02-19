<?php
namespace dbtext\text;

use dbtext\storage\DbtextCollectionManager;
use n2n\persistence\orm\CascadeType;
use n2n\persistence\orm\annotation\AnnoManyToOne;
use n2n\persistence\orm\annotation\AnnoOneToMany;
use n2n\persistence\orm\annotation\AnnoTable;
use n2n\reflection\ObjectAdapter;
use n2n\reflection\annotation\AnnoInit;

/**
 * Text holds Translations {@see TextT}.
 * @package dbtext\text
 */
class Text extends ObjectAdapter {
	private static function _annos(AnnoInit $ai) {
		$ai->c(new AnnoTable('dbtext_text_text'));
		$ai->p('textTs', new AnnoOneToMany(TextT::getClass(), 'text', CascadeType::ALL, null, true));
		$ai->p('group', new AnnoManyToOne(Group::getClass()));
	}

	/**
	 * @var int
	 */
	private $id;
	/**
	 * @var string
	 */
	private $key;
/**
	 * @var TextT[] $textTs
	 */
	private $textTs;
/**
	 * @var Group $group
	 */
	private $group;

	/**
	 * @param int $id
	 * @param TextT[] $textTs
	 */
	public function __construct(int $id = null, string $key = null, Group $group = null, array $textTs = null) {
		$this->id = $id;
		$this->key = $key;
		$this->textTs = $textTs;
		$this->group = $group;
	}

	private function _postUpdate(DbtextCollectionManager $dbtextCollectionManager) {
		$dbtextCollectionManager->clearCache($this->group->getNamespace());
	}
	
	public function getId() {
		return $this->id;
	}
	
	public function setId(int $id) {
		$this->id = $id;
	}

	public function getKey() {
		return $this->key;
	}

	public function setKey($key) {
		$this->key = $key;
	}

	/**
	 * @return TextT []
	 */
	public function getTextTs() {
		return $this->textTs;
	}

	/**
	 * @param \ArrayObject $textTs
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
}