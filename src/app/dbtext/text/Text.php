<?php
namespace dbtext\text;

use dbtext\storage\DbtextCollectionManager;
use n2n\persistence\orm\annotation\AnnoColumn;
use n2n\persistence\orm\annotation\AnnoManyToOne;
use n2n\persistence\orm\annotation\AnnoOneToMany;
use n2n\persistence\orm\annotation\AnnoTable;
use n2n\persistence\orm\CascadeType;
use n2n\reflection\annotation\AnnoInit;
use n2n\reflection\ObjectAdapter;
use rocket\attribute\EiType;
use rocket\attribute\MenuItem;
use rocket\attribute\EiPreset;
use rocket\spec\setup\EiPresetMode;
use rocket\ei\util\Eiu;
use dbtext\PlaceholderEiPropNature;
use rocket\attribute\impl\EiSetup;
use rocket\attribute\EiDisplayScheme;

/**
 * Text holds Translations {@see TextT}.
 * @package dbtext\text
 */
#[EiType(label: 'Text', pluralLabel: 'Texts')]
#[MenuItem(name: 'Alle Texte', groupName: 'Tools')]
#[EiPreset(EiPresetMode::EDIT, excludeProps: ['id'])]
class Text extends ObjectAdapter {
	private static function _annos(AnnoInit $ai) {
		$ai->c(new AnnoTable('dbtext_text'));
		$ai->p('placeholdersJson', new AnnoColumn('placeholders'));
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
	 * @var Group $group
	 */
	private $group;
	/**
	 * @var TextT[] $textTs
	 */
	private $textTs;
	/**
	 * The available Placeholders that were found.
	 * Placeholders are updated when found only if
	 * the config specifies modifyOnRequest = true.
	 *
	 * @var string $placeholdersJson
	 */
	private $placeholdersJson = '{}';

	/**
	 * @param int $id
	 * @param TextT[] $textTs
	 */
	public function __construct(string $key = null, Group $group = null, array $args = null, array $textTs = null) {
		$this->key = $key;
		$this->textTs = $textTs;
		$this->group = $group;
	}

	private function _postUpdate(DbtextCollectionManager $dbtextCollectionManager) {
		$dbtextCollectionManager->clearCache($this->group->getNamespace());
	}

	/**
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @return string
	 */
	public function getKey() {
		return $this->key;
	}

	/**
	 * @param string $key
	 */
	public function setKey(string $key = null) {
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
	 * @return array
	 */
	public function getPlaceholders() {
		return json_decode($this->placeholdersJson ?? '[]', true);
	}

	/**
	 * @param array $placeholdersJson
	 */
	public function setPlaceholders(array $placeholdersJson) {
		$this->placeholdersJson = json_encode($placeholdersJson ?? '[]');
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

	/**
	 * @param Eiu $eiu
	 * @return void
	 */
	#[EiSetup]
	static function eiSetup(Eiu $eiu) {
		$eiu->mask()->addProp((new PlaceholderEiPropNature())->setLabel('Verfügbare Textbausteine'));
	}
}