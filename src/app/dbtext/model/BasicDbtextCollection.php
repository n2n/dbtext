<?php
namespace dbtext\model;

use dbtext\storage\GroupData;
use n2n\l10n\N2nLocale;
use n2n\l10n\TextCollection;

/**
 * Manages {@see GroupData}.
 */
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
	 * {@inheritDoc}
	 */
	public function t(string $key, array $args = null, N2nLocale ...$n2nLocales): string {
		if (!$this->has($key)) {
			$this->groupData->add($key);
		}
		
		return TextCollection::fillArgs($this->groupData->t($key, ...$n2nLocales), $args);
	}

	/**
	 * {@inheritDoc}
	 */
	public function tf(string $key, array $args = null, N2nLocale ...$n2nLocales): string {
		if (!$this->has($key)) {
			$this->groupData->add($key);
		}
		
		$text = $this->groupData->t($key, ...$n2nLocales);

		$text = @sprintf($text, ...$args);

		if (!!$text) {
			return $text;
		}

		return $key;
	}

	/**
	 * {@inheritDoc}
	 */
	public function has(string $key): bool {
		return $this->groupData->has($key);
	}
}