<?php
namespace dbtext\model;

use dbtext\storage\GroupData;
use n2n\l10n\N2nLocale;

class TranslatedDbTextCollection implements DbTextCollection {
	/**
	 * @var N2nLocale[] $preferedN2nLocales
	 */
	private $preferedN2nLocales;

	/**
	 * @var DbTextCollection
	 */
	private $decorated;

	/**
	 * @param DbTextCollection $groupText
	 * @param array $preferedN2nLocales
	 */
	public function __construct(DbTextCollection $groupText, array $preferedN2nLocales) {
		$this->preferedN2nLocales = $preferedN2nLocales;
		$this->decorated = $groupText;
	}

	/**
	 * @inheritdoc
	 */
	public function t(string $id, array $args, N2nLocale ...$n2nLocale): string {
		return $this->decorated->t($id, $args, $this->preferedN2nLocales);
	}

	/**
	 * @inheritdoc
	 */
	public function tf(string $id, array $args, N2nLocale ...$n2nLocale): string {
		return $this->decorated->tf($id, $args, $this->preferedN2nLocales);
	}
}