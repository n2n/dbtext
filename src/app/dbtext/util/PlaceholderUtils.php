<?php
namespace dbtext\util;

use n2n\util\StringUtils;

/**
 * Utility class for placeholder-related operations.
 */
class PlaceholderUtils {
	/**
	 * Appends unused arguments when there's no translation.
	 * Determines unused arguments by comparing provided args with the key structure.
	 *
	 * @param string $prettyKey
	 * @param string $originalKey
	 * @param array $args
	 * @return string
	 */
	public static function appendUnusedArguments(string $prettyKey, string $originalKey, array $args): string {
		if (empty($args)) {
			return $prettyKey;
		}
		
		$unusedArgs = self::getUnusedArgumentsForMissingTranslation($originalKey, $args);
		if (!empty($unusedArgs)) {
			$prettyKey .= ' ' . self::formatProvidedArguments($unusedArgs);
		}
		
		return $prettyKey;
	}

	/**
	 * Formats provided arguments with prettyfied keys.
	 *
	 * @param array $args
	 * @return string
	 */
	private static function formatProvidedArguments(array $args): string {
		$formattedArgs = [];
		foreach ($args as $key => $value) {
			$formattedArgs[] = StringUtils::pretty($key) . ': ' . $value;
		}

		return '[' . implode(', ', $formattedArgs) . ']';
	}

	/**
	 * Determines which arguments are unused for missing translations.
	 * Checks each argument and includes it if it's NOT found in the key structure.
	 *
	 * @param string $key
	 * @param array $args
	 * @return array
	 */
	private static function getUnusedArgumentsForMissingTranslation(string $key, array $args): array {
		$unusedArgs = [];
		foreach ($args as $argKey => $argValue) {
			if (str_contains($key, $argKey)) {
				continue;
			}
			$unusedArgs[$argKey] = $argValue;
		}

		return $unusedArgs;
	}
} 