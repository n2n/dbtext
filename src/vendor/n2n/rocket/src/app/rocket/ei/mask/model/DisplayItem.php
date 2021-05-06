<?php
namespace rocket\ei\mask\model;

use rocket\ei\manage\DefPropPath;
use n2n\util\ex\IllegalStateException;
use n2n\util\type\ArgUtils;
use n2n\l10n\Lstr;
use rocket\core\model\Rocket;
use n2n\l10n\N2nLocale;
use rocket\si\meta\SiStructureType;

class DisplayItem {
	
	protected $label;
	protected $helpText;
	protected $moduleNamespace;
	protected $siStructureType;
	protected $autonomic = false;
	protected $defPropPath;
	protected $displayStructure;

	private function __construct() {
	}

	/**
	 * @param DefPropPath $defPropPath
	 * @return DisplayItem
	 */
	public static function create(DefPropPath $defPropPath, ?string $siStructureType/*, bool $autonomic = false*/) {
		$orderItem = new DisplayItem();
		ArgUtils::valEnum($siStructureType, SiStructureType::all(), null, true);
		$orderItem->siStructureType = $siStructureType;
		$orderItem->defPropPath = $defPropPath;
// 		$orderItem->autonomic = $autonomic;
		return $orderItem;
	}
	
// 	/**
// 	 * @param DefPropPath $defPropPath
// 	 * @return DisplayItem
// 	 * @deprecated
// 	 */
// 	public static function createFromDefPropPath(DefPropPath $defPropPath, string $siStructureType = null) {
// 		$orderItem = new DisplayItem();
// 		ArgUtils::valEnum($siStructureType, SiStructureType::all(), null, true);
// 		$orderItem->siStructureType = $siStructureType;
// 		$orderItem->defPropPath = $defPropPath;
// 		return $orderItem;
// 	}

	/**
	 * @param DisplayStructure $displayStructure
	 * @return DisplayItem
	 */
	public static function createFromDisplayStructure(DisplayStructure $displayStructure, string $siStructureType/*, bool $autonomic = false*/,
			string $label = null, string $helpText = null, string $moduleNamespace = null) {
		$displayItem = new DisplayItem();
		$displayItem->displayStructure = $displayStructure;
		ArgUtils::valEnum($siStructureType, SiStructureType::all());
		$displayItem->siStructureType = $siStructureType;
		$displayItem->label = $label;
		$displayItem->helpText = $helpText;
		$displayItem->moduleNamespace = $moduleNamespace;
		return $displayItem;
	}
	
	/**
	 * @param string|null $type
	 * @param string|null $labelLstr
	 * @return DisplayItem
	 */
	public function copy(string $siStructureType = null, array $attrs = null/*, Lstr $labelLstr = null*/) {
		$displayItem = new DisplayItem();
		$displayItem->displayStructure = $this->displayStructure;
		$displayItem->defPropPath = $this->defPropPath;
		ArgUtils::valEnum($siStructureType, SiStructureType::all(), null, true);
		$displayItem->siStructureType = $siStructureType ?? $this->siStructureType;
// 		$displayItem->labelLstr = $labelLstr ?? $this->labelLstr;
		return $displayItem;
	}
	
	/**
	 * @return Lstr|null
	 */
	public function getLabelLstr() {
		if (!$this->hasDisplayStructure()) {
			throw new IllegalStateException('No labels for DefPropPath DisplayItem.');
		}
		
		if ($this->label === null) return null;
		
		if ($this->moduleNamespace === null) {
			return Lstr::create($this->label);
		}
				
		return Rocket::createLstr($this->label, $this->moduleNamespace);
	}
	
	/**
	 * @return string|null
	 */
	public function getLabel() {
		return $this->label;
	}
	
	/**
	 * @return Lstr|null
	 */
	public function getHelpTextLstr() {
		if (!$this->hasDisplayStructure()) {
			throw new IllegalStateException('No helpTexts for DefPropPath DisplayItem.');
		}
		
		if ($this->helpText === null) return null;
		
		if ($this->moduleNamespace === null) {
			return Lstr::create($this->helpText);
		}
		
		return Rocket::createLstr($this->helpText, $this->moduleNamespace);
	}
	
	/**
	 * @return string|null
	 */
	public function getHelpText() {
		return $this->helpText;
	}
	
	/**
	 * @return string|null
	 */
	public function getModuleNamespace() {
		return $this->moduleNamespace;
	}
	
	/**
	 * @param N2nLocale $n2nLocale
	 * @return string|null
	 */
	public function translateLabel(N2nLocale $n2nLocale) {
		$lstr = $this->getLabelLstr();
		if ($lstr !== null) {
			return $lstr->t($n2nLocale);
		}
		return null;
	}
	
// 	/**
// 	 * @param N2nLocale $n2nLocale
// 	 * @return string|null
// 	 */
// 	public function translateHelpText(N2nLocale $n2nLocale) {
// 		if ($this->helpText === null) return null;
		
// 		if ($this->moduleNamespace === null) {
// 			return $this->helpText;
// 		}
		
// 		return Rocket::createLstr($this->helpText, $this->moduleNamespace)->t($n2nLocale);
// 	}

	/**
	 * @return string|null
	 */
	public function getSiStructureType() {
		return $this->siStructureType;
	}
	
	/**
	 * @return array|null
	 */
	public function getAttrs() {
		return $this->attrs;
	}

	/**
	 * @return boolean
	 */
	public function isGroup() {
		return in_array($this->siStructureType, SiStructureType::groups());
	}

	public function hasDisplayStructure() {
		return $this->displayStructure !== null;
	}

	/**
	 * @return DisplayStructure
	 * @throws IllegalStateException
	 */
	public function getDisplayStructure() {
		if ($this->displayStructure !== null) {
			return $this->displayStructure;
		}

		throw new IllegalStateException();
	}

	/**
	 * @throws IllegalStateException
	 * @return DefPropPath
	 */
	public function getDefPropPath() {
		if ($this->defPropPath !== null) {
			return $this->defPropPath;
		}

		throw new IllegalStateException();
	}
}