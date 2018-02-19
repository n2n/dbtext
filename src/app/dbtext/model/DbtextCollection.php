<?php
namespace dbtext\model;

use n2n\l10n\N2nLocale;

interface DbtextCollection {
	/**
	 * Finds most fitting {@see TextT} by locales provided and returns modified {@see TextT::$str}.
	 * {@see TextT::$str} args are replaced by {@see TextCollection::fillArgs()}.
	 *
	 * @param string $id
	 * @param array $args
	 * @param N2nLocale[] ...$n2nLocales
	 * @return string
	 */
	public function t(string $id, array $args = null, N2nLocale ...$n2nLocales): string;

	/**
	 * Finds most fitting {@see TextT} by locales provided and returns modified {@see TextT::$str}.
	 * {@see TextT::$str} args are replaced by the printf method.
	 *
	 * @param string $id
	 * @param array $args
	 * @param N2nLocale[] ...$n2nLocales
	 * @return string
	 */
	public function tf(string $id, array $args = null, N2nLocale ...$n2nLocales): string;
}