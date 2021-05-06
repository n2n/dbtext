<?php 
namespace rocket\ei\manage\idname;

use n2n\l10n\N2nLocale;
use rocket\ei\manage\EiObject;
use n2n\core\container\N2nContext;
use rocket\ei\util\Eiu;
use rocket\ei\EiPropPath;
use rocket\ei\manage\DefPropPath;
use n2n\util\thread\RecursionAsserters;

class SummarizedStringBuilder {
	const KNOWN_STRING_FIELD_OPEN_DELIMITER = '{';
	const KNOWN_STRING_FIELD_CLOSE_DELIMITER = '}';
	
	private $identityStringPattern;
	private $n2nContext;
	private $n2nLocale;
	
	private $placeholders = array();
	private $replacements = array();
	
	public function __construct(string $identityStringPattern, N2nContext $n2nContext, N2nLocale $n2nLocale) {
		$this->identityStringPattern = $identityStringPattern;
		$this->n2nContext = $n2nContext;
		$this->n2nLocale = $n2nLocale;
	}
	
	/**
	 * @param string $identityStringPattern
	 * @param IdNameDefinition $idNameDefinition
	 * @return \rocket\ei\manage\DefPropPath[]
	 */
	static function detectUsedDefPropPaths(string $identityStringPattern, IdNameDefinition $idNameDefinition) {
		$usedDefPropPaths = [];
		foreach (array_keys($idNameDefinition->getAllIdNameProps()) as $defGuiPropPathStr) {
			if (false === strpos($identityStringPattern, self::createPlaceholerFromStr($defGuiPropPathStr))) {
				continue;
			}
			
			$usedDefPropPaths[$defGuiPropPathStr] = DefPropPath::create($defGuiPropPathStr);
		}
		return $usedDefPropPaths;
	}
	
	public function replaceFields(array $baseEiPropPaths, IdNameDefinition $idNameDefinition, EiObject $eiObject = null) {
		if (!RecursionAsserters::unique(self::class)->tryPush((string) $idNameDefinition->getEiMask()->getEiTypePath())) {
			return;
		}
		
		foreach ($idNameDefinition->getIdNameProps() as $eiPropPathStr => $idNameProp) {
			$eiPropPath = EiPropPath::create($eiPropPathStr);
			$placeholder = self::createPlaceholder($this->createDefPropPath($baseEiPropPaths, $eiPropPath));
			
			if (false === strpos($this->identityStringPattern, $placeholder)) {
				continue;
			}
			
			$this->placeholders[] = $placeholder;
			if ($eiObject === null) {
				$this->replacements[] = '';
			} else {
				$eiu = new Eiu($this->n2nContext, $eiObject, $idNameDefinition->getEiMask(), $eiPropPath);
				$this->replacements[] = $idNameProp->buildIdentityString($eiu, $this->n2nLocale);
			}
		}
		
		foreach ($idNameDefinition->getIdNamePropForks() as $eiPropPathStr => $idNamePropFork) {
			$forkedIdNameDefinition = $idNamePropFork->getForkedIdNameDefinition();
			$eiPropPath = EiPropPath::create($eiPropPathStr);
			
			if ($forkedIdNameDefinition === null) continue;
			
			$forkedEiFieldSource = null;
			if ($eiObject !== null) {
				$eiu = new Eiu($this->n2nContext, $eiObject, $idNameDefinition->getEiMask(), $eiPropPath);
				$forkedEiFieldSource = $idNamePropFork->determineForkedEiObject($eiu);
			}
			
			$ids = $baseEiPropPaths;
			$ids[] = $eiPropPath;
			$this->replaceFields($ids, $forkedIdNameDefinition, $forkedEiFieldSource);
		}
		
		RecursionAsserters::unique(self::class)->pop((string) $idNameDefinition->getEiMask()->getEiTypePath());
	}
	
	private function createDefPropPath(array $baseIds, $id) {
		$ids = $baseIds;
		$ids[] = $id;
		return new DefPropPath($ids);
	}
	
	public static function createPlaceholder($eiPropPath) {
		return self::KNOWN_STRING_FIELD_OPEN_DELIMITER . DefPropPath::create($eiPropPath)
				. self::KNOWN_STRING_FIELD_CLOSE_DELIMITER;
	}
	
	/**
	 * @param string $defPropPathStr
	 * @return string
	 */
	private static function createPlaceholerFromStr($defPropPathStr) {
		return self::KNOWN_STRING_FIELD_OPEN_DELIMITER . $defPropPathStr
				. self::KNOWN_STRING_FIELD_CLOSE_DELIMITER;
	}
	
	public function __toString(): string {
		return str_replace($this->placeholders, $this->replacements, $this->identityStringPattern);
	}
}
