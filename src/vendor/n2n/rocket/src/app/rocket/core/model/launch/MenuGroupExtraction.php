<?php
namespace rocket\core\model\launch;

use n2n\util\type\ArgUtils;

class MenuGroupExtraction {
	private $label;
	private $launchPadLabels = array();
	
	public function __construct(string $label) {
		$this->label = $label;
	}
	
	public function setLabel(string $label) {
		$this->label = $label;
	}
	
	public function getLabel(): string {
		return $this->label;
	}
	
	public function addLaunchPad(string $launchPadId, string $label = null) {
		$this->launchPadLabels[$launchPadId] = $label;
	}
	
	/**
	 * @param string[] $launchPadLabels
	 */
	public function setLaunchPadLabels(array $launchPadLabels) {
		ArgUtils::valArray($launchPadLabels, ['string', 'null']);
		$this->launchPadLabels = $launchPadLabels;
	}
	
	/**
	 * @return string[]
	 */
	public function getLaunchPadLabels() {
		return $this->launchPadLabels;
	}
}
