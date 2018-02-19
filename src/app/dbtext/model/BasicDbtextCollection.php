<?php
namespace dbtext\model;

use dbtext\storage\GroupData;
use n2n\l10n\N2nLocale;
use n2n\l10n\TextCollection;

class BasicDbtextCollection implements DbtextCollection {
	/**
	 * @var GroupData
	 */
	private $groupData;

	/**
	 * @param GroupData $groupData
	 */
	public function __construct(GroupData $groupData) {
		$this->groupData = $groupData;
	}

	/**
	 * @inheritdoc
	 */
	public function t(string $id, array $args = null, N2nLocale ...$n2nLocales): string {
		if (!$this->groupData->has($id)) {
			$this->groupData->add($id);
		}
		
		return TextCollection::fillArgs($this->groupData->t($id, ...$n2nLocales), $args);
	}

	/**
	 * @inheritdoc
	 */
	public function tf(string $id, array $args = null, N2nLocale ...$n2nLocales): string {
		$text = $this->groupData->t($id, ...$n2nLocales);

		$text = @sprintf($text, ...$args);

		if (!!$text) {
			return $text;
		}

		return $id;
	}
}