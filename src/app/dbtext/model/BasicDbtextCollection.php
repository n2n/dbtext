<?php
namespace dbtext\model;

use dbtext\storage\GroupData;
use n2n\l10n\N2nLocale;
use n2n\l10n\TextCollection;
use n2n\util\StringUtils;

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
	public function t(string $key, ?array $args = null, N2nLocale ...$n2nLocales): string {
		$n2nLocales = array_merge($n2nLocales, $this->n2nLocales);
		$n2nLocales[] = N2nLocale::getFallback();
		$args = $args ?? [];

		if (!$this->has($key)) {
			$this->groupData->add($key, $args);
		} else if (!$this->groupData->equalsPlaceholders($key, $args)) {
			$this->groupData->changePlaceholders($key, $args);
		}

		$text = $this->groupData->find($key, ...$n2nLocales);
		if ($text === null) {
			$prettyKey = DbtextService::prettyKey($key, $args);
			return $this->appendArguments($prettyKey, $args);
		}

		$processedText = TextCollection::fillArgs($text, $args);
		
		return $processedText;
	}

	/**
	 * {@inheritDoc}
	 */
	public function tf(string $key, ?array $args = null, N2nLocale ...$n2nLocales): string {
		$n2nLocales = array_merge($n2nLocales, $this->n2nLocales);
		$n2nLocales[] = N2nLocale::getFallback();

		if (!$this->has($key)) {
			$this->groupData->add($key, $args);
		}

		$text = $this->groupData->find($key, ...$n2nLocales);
		if ($text === null) {
			return DbtextService::prettyKey($key, $args);
		}

		$args = $args ?? [];
		$processedText = @sprintf($text, ...$args);

		if (!!$processedText) {
			return $processedText;
		}

		return $key;
	}

	/**
	 * {@inheritDoc}
	 */
	public function has(string $key): bool {
		return $this->groupData->has($key);
	}

	/**
	 * @throws \OutOfBoundsException
	 * @param string $key
	 * @return array
	 */
	public function getPlaceholderNamesOfKey(string $key): array {
		return $this->groupData->getPlaceholderNamesOfKey($key);
	}

	public function getKeys(): array {
		return $this->groupData->getKeys();
	}

	/**
	 * Append provided arguments for keys that are not translated.
	 * !!!Don't use on translated keys!!!
	 *
	 * @param string $prettyKey∆
	 * @param array $args
	 * @return string
	 */
	private function appendArguments(string $prettyKey, array $args): string {
		if (empty($args)) {
			return $prettyKey;
		}

		$prettyKey .= ' ' . $this->formatProvidedArguments($args);
		
		return $prettyKey;
	}

	/**
	 * Formats provided arguments with prettied keys.
	 *
	 * @param array $args
	 * @return string
	 */
	private function formatProvidedArguments(array $args): string {
		$formattedArgs = [];
		foreach ($args as $key => $value) {
			$prettyKey = StringUtils::pretty($key);
			$formattedArgs[] = '[' . $prettyKey . ':' . $value . ']';
		}
		
		return implode(' ', $formattedArgs);
	}
}
