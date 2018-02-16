<?php
namespace dbtext\model;

use dbtext\DbTextUtils;
use dbtext\storage\GroupData;
use n2n\l10n\N2nLocale;
use n2n\l10n\TextCollection;

class BasicDbTextCollection implements DbTextCollection {
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
	public function t(string $id, array $args = null, N2nLocale ...$n2nLocale): string {
		return TextCollection::fillArgs($this->groupData->t($id, ...$n2nLocale), $args);
	}

	/**
	 * @inheritdoc
	 */
	public function tf(string $id, array $args = null, N2nLocale ...$n2nLocale): string {
		try {
			return vsprintf($this->groupData->t($id, ...$n2nLocale), $args);
		} catch (WarningError $e) {
			throw new PrintFException($e->getMessage());
		}
	}
}