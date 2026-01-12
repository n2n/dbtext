<?php

namespace dbtext;

use n2n\spec\valobj\scalar\StringValueObject;
use n2n\util\StringUtils;
use n2n\util\JsonDecodeFailedException;
use n2n\util\type\attrs\DataMap;
use n2n\spec\valobj\err\IllegalValueException;
use n2n\util\type\attrs\AttributesException;
use n2n\util\magic\MagicContext;
use n2n\l10n\N2nLocale;
use dbtext\model\DbtextService;
use n2n\util\type\ArgUtils;
use n2n\util\ex\ExUtils;
use n2n\l10n\LcStr;

class LcText implements StringValueObject, LcStr {
	private ?string $text = null;
	private ?string $textCode = null;
	private ?array $args = null;
	private ?string $namespace = null;

	function __construct(string $value) {
		try {
			$dataMap = new DataMap(StringUtils::jsonDecode($value, true));
			if (null !== ($text = $dataMap->optString('text'))) {
				$this->text = $text;
			} else {
				$this->textCode = $dataMap->reqString('textCode');
				$this->args = $dataMap->reqArray('args', 'string');
				$this->namespace = $dataMap->reqString('namespace');
			}
		} catch (JsonDecodeFailedException|AttributesException $e) {
			throw new IllegalValueException($value, previous: $e);
		}
	}

	function toScalar(): string {
		return self::serialize($this->text, $this->textCode, $this->args, $this->namespace);
	}

	function __toString(): string {
		return $this->toScalar();
	}

	function composeString(MagicContext $magicContext, ?N2nLocale $n2nLocale = null): string {
		if (null !== $this->text) {
			return $this->text;
		}

		return $magicContext->lookup(DbtextService::class)->t($this->namespace, $this->textCode, $this->args,
				$n2nLocale ?? $magicContext->lookup(N2nLocale::class, false) ?? N2nLocale::getDefault());
	}

	/**
	 * @deprecated use {@link self::composeString()}
	 */
	function t(MagicContext $magicContext, ?N2nLocale $n2nLocale = null): string {
		return $this->composeString($magicContext, $n2nLocale);
	}

	static function dbtext(string $namespace, string $textCode, array $args = []): LcText {
		$args = array_map(fn ($arg) => StringUtils::strOf($arg), $args);
		return ExUtils::try(fn () => new LcText(self::serialize(null, $textCode, $args, $namespace)));
	}

	static function text(string $text): LcText {
		return ExUtils::try(fn () => new LcText(self::serialize($text, null, null, null)));
	}

	private static function serialize(?string $text, ?string $textCode, ?array $args, ?string $namespace): string {
		return json_encode(['text' => $text, 'textCode' => $textCode, 'args' => $args, 'namespace' => $namespace]);
	}
}
