<?php
namespace dbtext\util;

/**
 * Utility class for placeholder-related operations.
 */
class PlaceholderUtils {
	
	/**
	 * Extracts placeholders from text using regex.
	 *
	 * @param string $text
	 * @return array
	 */
	public static function extractPlaceholders(string $text): array {
		preg_match_all('/\{([^}]+)}/', $text, $matches);
		return $matches[1] ?? [];
	}
	
	/**
	 * Identifies unused arguments by comparing provided args with placeholders in text.
	 *
	 * @param string $text
	 * @param array $args
	 * @return array
	 */
	public static function getUnusedArguments(string $text, array $args): array {
		$usedPlaceholders = self::extractPlaceholders($text);
		
		$unusedArgs = [];
		foreach ($args as $key => $value) {
			if (!in_array($key, $usedPlaceholders)) {
				$unusedArgs[$key] = $value;
			}
		}
		
		return $unusedArgs;
	}
} 