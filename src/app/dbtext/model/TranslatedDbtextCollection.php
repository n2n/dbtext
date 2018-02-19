<?php
namespace dbtext\model;

use n2n\l10n\N2nLocale;

class TranslatedDbtextCollection implements DbtextCollection {
	/**
	 * @var N2nLocale[] $preferedN2nLocales
	 */
	private $preferedN2nLocales;

	/**
	 * @var DbtextCollection
	 */
	private $decorated;

	/**
	 * @param DbtextCollection $textCollection
	 * @param array $preferedN2nLocales
	 */
	public function __construct(DbtextCollection $textCollection, array $preferedN2nLocales) {
		$this->preferedN2nLocales = $preferedN2nLocales;
		$this->decorated = $textCollection;
	}

	/**
	 * @inheritdoc
	 */
	public function t(string $id, array $args, N2nLocale ...$n2nLocales): string {
		return $this->decorated->t($id, $args, $this->preferedN2nLocales);
	}

	/**
	 * @inheritdoc
	 */
	public function tf(string $id, array $args, N2nLocale ...$n2nLocales): string {
		return $this->decorated->tf($id, $args, $this->preferedN2nLocales);
	}
}