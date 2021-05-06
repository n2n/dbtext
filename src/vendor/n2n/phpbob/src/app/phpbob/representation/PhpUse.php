<?php
namespace phpbob\representation;

use phpbob\Phpbob;
use n2n\util\type\ArgUtils;
use phpbob\representation\traits\PrependingCodeTrait;
use phpbob\representation\traits\PhpNamespaceElementTrait;

class PhpUse {
	use PrependingCodeTrait;
	use PhpNamespaceElementTrait;
	
	const TYPE_FUNCTION = 'function';
	const TYPE_CONST = 'const';
	
	private $typeName;
	private $type;
	private $alias;
	
	public function __construct(PhpFile $phpFile, string $typeName, PhpNamespace $phpNamespace = null) {
		$this->phpFile = $phpFile;
		$this->typeName = $typeName;
		$this->phpNamespace = $phpNamespace;
	}
	
	public function getPhpFile() {
		return $this->phpFile;
	}
	
	public function getTypeName() {
		return $this->typeName;
	}

	public function getType() {
		return $this->type;
	}

	public function setType(string $type = null) {
		ArgUtils::valEnum($type, self::getTypes(), null, true);
		
		$this->type = $type;
		
		return $this;
	}

	public function getAlias() {
		return $this->alias;
	}

	public function setAlias(string $alias = null) {
		if (null !== $alias && 
				$this->determinePhpNamespaceElementCreator()->hasPhpUseAlias($alias)) {
			throw new \InvalidArgumentException('alias with name ' . $alias . ' already defined.');
		}
		
		$this->alias = $alias;
		
		
		return $this;
	}
	
	public function hasAlias() {
		return null !== $this->alias;
	}

	public function __toString() {
		$string = $this->getPrependingString() . Phpbob::KEYWORD_USE;
		
		if (null !== $this->type) {
			$string .= ' ' . $this->type;
		}
		
		$string .= ' ' . $this->typeName;
		
		if (null !== $this->alias) {
			$string .= ' ' . Phpbob::KEYWORD_AS . ' ' . $this->alias;
		}
		
		return $string . Phpbob::SINGLE_STATEMENT_STOP . PHP_EOL;
	}
	
	public static function getTypes() {
		return array(self::TYPE_FUNCTION, self::TYPE_CONST);
	}
}