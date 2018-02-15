<?php
namespace dbtext\model;

use n2n\l10n\N2nLocale;

interface CategoryText {
	/**
	 * Finds most fitting {@see TextT} by locales provided and returns modified {@see TextT::$str}.
	 * {@see TextT::$str} args are replaced by {@see TextCollection::fillArgs()}.
	 *
	 * @param string $id
	 * @param array $args
	 * @param N2nLocale[] ...$n2nLocale
	 * @return mixed
	 */
	public function t(string $id, array $args, N2nLocale ...$n2nLocale);

	/**
	 * Finds most fitting {@see TextT} by locales provided and returns modified {@see TextT::$str}.
	 * {@see TextT::$str} args are replaced by {@see DbTextUtils::processTextF()}.
	 *
	 * @param string $id
	 * @param array $args
	 * @param N2nLocale[] ...$n2nLocale
	 * @return mixed
	 */
	public function tf(string $id, array $args, N2nLocale ...$n2nLocale);
}