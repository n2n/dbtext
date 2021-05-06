<?php
namespace rocket\ei\manage\gui;

use n2n\l10n\Lstr;
use n2n\util\type\ArgUtils;
use rocket\si\meta\SiStructureType;

class DisplayDefinition {
	private $siStructureType;
	private $defaultDisplayed;
	private $overwriteLabel;
	private $overwriteHelpText;
	
	/**
	 * @param Lstr $labelLstr
	 * @param string $siStructureType
	 * @param bool $defaultDisplayed
	 */
	public function __construct(string $siStructureType, bool $defaultDisplayed, string $overwriteLabel = null, string $overwriteHelpText = null) {
		ArgUtils::valEnum($siStructureType, SiStructureType::all());
		
		$this->siStructureType = $siStructureType;
		$this->defaultDisplayed = $defaultDisplayed;
		$this->overwriteLabel = $overwriteLabel;
		$this->overwriteHelpText = $overwriteHelpText;
	}
	
	/**
	 * @return string
	 */
	public function getSiStructureType(): string {
		return $this->siStructureType;
	}
	
	/**
	 * @return bool
	 */
	public function isDefaultDisplayed() {
		return $this->defaultDisplayed;
	}
	
	public function getOverwriteLabel() {
		return $this->overwriteLabel;
	}
	
	public function getOverwriteHelpText() {
		return $this->overwriteHelpText;
	}
}
