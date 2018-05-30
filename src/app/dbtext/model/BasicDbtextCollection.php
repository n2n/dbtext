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
	 * @var GroupData $groupData
	 */
	private $groupData;
	/**
	 * @var N2nLocale[] $n2nLocale
	 */
	private $n2nLocales;

	/**
	 * @param GroupData $groupData
	 */
	public function __construct(GroupData $groupData, N2nLocale ...$n2nLocales) {
		$this->groupData = $groupData;
		$this->n2nLocales = $n2nLocales;
	}

	/**
	 * {@inheritDoc}
	 */
	public function t(string $key, array $args = null, N2nLocale ...$n2nLocales): string {
		$n2nLocales = array_merge($n2nLocales, $this->n2nLocales);
		$n2nLocales[] = N2nLocale::getFallback();

		if (!$this->has($key)) {
			$this->groupData->add($key);
		}

		if (!$this->groupData->equalsPlaceholders($key, $args)) {
			$this->groupData->changePlaceholders($key, $args);
		}

		return TextCollection::fillArgs($this->groupData->t($key, ...$n2nLocales), $args);
	}

	/**
	 * {@inheritDoc}
	 */
	public function tf(string $key, array $args = null, N2nLocale ...$n2nLocales): string {
		$n2nLocales = array_merge($n2nLocales, $this->n2nLocales);
		$n2nLocales[] = N2nLocale::getFallback();

		if (!$this->has($key)) {
			$this->groupData->add($key, $args);
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