<?php
namespace dbtext;

use dbtext\exception\PrintFException;
use n2n\core\err\WarningError;
use n2n\l10n\N2nLocale;

class DbTextUtils {

	/**
	 * @param string $text
	 * @param array $args
	 */
	public static function processTextF(string $text, array $args = null): string {
		try {
			return vsprintf($text, $args);
		} catch (WarningError $e) {
			throw new PrintFException($e->getMessage());
		}
	}
}