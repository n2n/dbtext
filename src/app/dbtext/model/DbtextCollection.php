<?php
namespace dbtext\model;

use n2n\l10n\N2nLocale;

interface DbtextCollection {
	/**
	 * Finds {@see TextT} by locales provided and returns modified {@see TextT::$str}.
	 * {@see TextT::$str} args are replaced by {@see TextCollection::fillArgs()}.
	 *
	 * @param string $key
	 * @param array $args
	 * @param N2nLocale[] ...$n2nLocales
	 * @return string
	 */
	public function t(string $key, array $args = null, N2nLocale ...$n2nLocales): string;

	/**
	 * Finds {@see TextT} by locales provided and returns {@see TextT::$str}.
	 * {@see TextT::$str} args are replaced by the printf method.
	 *
	 * @param string $key
	 * @param array $args
	 * @param N2nLocale[] ...$n2nLocales
	 * @return string
	 */
	public function tf(string $key, array $args = null, N2nLocale ...$n2nLocales): string;

	/**
	 * True when DbtextCollection has a {@see Text}.
	 *
	 * @param string $key
	 * @return bool
	 */
	public function has(string $key): bool;

	/**
	 * Returns the saved placeholders
	 *
	 * @param string $key
	 * @return array
	 */
	public function getPlaceholderNamesOfKey(string $key): array;

	/**
	 * @return string[]
	 */
	public function getKeys(): array;
}
