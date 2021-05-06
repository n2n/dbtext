<?php
namespace rocket\spec;

use n2n\util\type\ArgUtils;
use n2n\io\IoUtils;

class TypePath {
	const SEPARATOR = '&';
	
	private $typeId;
	private $typeExtensionId;
	
	/**
	 * @param string $eiTypeId
	 * @param string|null $eiTypeExtensionId
	 */
	function __construct(string $typeId, string $typeExtensionId = null) {
		ArgUtils::assertTrue(!IoUtils::hasSpecialChars($typeId));
		ArgUtils::assertTrue($typeExtensionId === null || !IoUtils::hasSpecialChars($typeExtensionId));
		
		$this->typeId = $typeId;
		$this->typeExtensionId = $typeExtensionId;
	}
	
	/**
	 * @return string
	 */
	function getTypeId() {
		return $this->typeId;
	}
	
	/**
	 * @return string|null
	 */
	function getEiTypeExtensionId() {
		return $this->typeExtensionId;
	}

	function __toString() {
		return $this->typeId 
				. ($this->typeExtensionId !== null ? self::SEPARATOR . $this->typeExtensionId : null);
	}
	
	/**
	 * @param mixed $obj
	 * @return boolean
	 */
	function equals($obj) {
		return $obj instanceof TypePath && $this->getTypeId() === $obj->getTypeId()
				&& $this->getEiTypeExtensionId() === $obj->getEiTypeExtensionId();
	}
	
	/**
	 * @param string|TypePath $expression
	 * @return TypePath
	 * @throws \InvalidArgumentException
	 */
	static function create($expression) {
		if ($expression instanceof TypePath) {
			return $expression;
		}
		
		if (is_scalar($expression)) {
			$parts = explode(self::SEPARATOR, $expression);
			try {
				return new TypePath($parts[0], $parts[1] ?? null);
			} catch (\InvalidArgumentException $e) {
				throw new \InvalidArgumentException('Invalid TypePath expression: ' . $expression);
			}
		}
		
		ArgUtils::valType($expression, ['string', TypePath::class]);
	}
	
	
}