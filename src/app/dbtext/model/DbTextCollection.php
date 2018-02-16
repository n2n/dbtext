<?php
namespace dbtext\model;

use n2n\l10n\N2nLocale;

interface DbTextCollection {
	/**
	 * Finds most fitting {@see TextT} by locales provided and returns modified {@see TextT::$str}.
	 * {@see TextT::$str} args are replaced by {@see TextCollection::fillArgs()}.
	 *
	 * @param string $id
	 * @param array $args
	 * @param N2nLocale[] ...$n2nLocale
	 * @return string
	 */
	public function t(string $id, array $args, N2nLocale ...$n2nLocale): string;

	/**
	 * Finds most fitting {@see TextT} by locales provided and returns modified {@see TextT::$str}.
	 * {@see TextT::$str} args are replaced by the printf method.
	 *
	 * @param string $id
	 * @param array $args
	 * @param N2nLocale[] ...$n2nLocale
	 * @return string
	 */
	public function tf(string $id, array $args, N2nLocale ...$n2nLocale): string;
}