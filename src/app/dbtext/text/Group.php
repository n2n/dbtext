<?php
namespace dbtext\text;

use dbtext\storage\DbtextCollectionManager;
use n2n\persistence\orm\annotation\AnnoId;
use n2n\persistence\orm\annotation\AnnoOneToMany;
use n2n\persistence\orm\CascadeType;
use n2n\persistence\orm\FetchType;
use n2n\reflection\annotation\AnnoInit;
use n2n\reflection\ObjectAdapter;
use n2n\persistence\orm\annotation\AnnoTable;
use rocket\attribute\EiPreset;
use rocket\attribute\EiType;
use rocket\attribute\MenuItem;
use rocket\spec\setup\EiPresetMode;

/**
 * Represents the namespace texts belong to.
 * @package dbtext\text
 */
#[EiType(label: 'Übersetzungen Gruppe', pluralLabel: 'Übersetzungen Gruppen')]
#[MenuItem(groupName: 'Tools')]
#[EiPreset(EiPresetMode::EDIT, readProps: ['texts'])]
class Group extends ObjectAdapter {
	private static function _annos(AnnoInit $ai) {
		$ai->c(new AnnoTable('dbtext_group'));
		$ai->p('namespace', new AnnoId(false));
		$ai->p('texts', new AnnoOneToMany(Text::getClass(), 'group', CascadeType::ALL, FetchType::LAZY));
	}

	/**
	 * @var string|null $namespace
	 */
	private ?string $namespace;
	/**
	 * @var string|null $label
	 */
	private ?string $label;
	/**
	 * @var Text[] $texts
	 */
	private \ArrayObject $texts;

	/**
	 * @param string $namespace
	 */
	public function __construct(string $namespace = null) {
		$this->namespace = $namespace;
		$this->label = $namespace;
		$this->texts = new \ArrayObject();
	}

	/**
	 * @return string
	 */
	public function getNamespace(): ?string {
		return $this->namespace;
	}

	/**
	 * @param string $namespace
	 */
	public function setNamespace(string $namespace) {
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
	public function setLabel(string $label) {
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
	public function setTexts(\ArrayObject $texts) {
		$this->texts = $texts;
	}

	public function addText(Text $text) {
		$this->texts[] = $text;
	}

	private function _postUpdate(DbtextCollectionManager $textCollectionManager) {
		$textCollectionManager->clearCache($this->namespace);
	}
}