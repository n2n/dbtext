<?php
namespace rocket\ei\util\gui;

use rocket\ei\util\EiuAnalyst;
use rocket\ei\manage\DefPropPath;
use rocket\ei\manage\api\ApiFieldCallId;

class EiuGuiField {
	private $defPropPath;
	private $eiuEntryGuiTypeDef;
	private $eiuAnalyst;
	
	/**
	 * @param DefPropPath $defPropPath
	 * @param EiuEntryGui $eiuEntryGuiTypeDef
	 * @param EiuAnalyst $eiuAnalyst
	 */
	function __construct(DefPropPath $defPropPath, EiuEntryGuiTypeDef $eiuEntryGuiTypeDef, EiuAnalyst $eiuAnalyst) {
		$this->defPropPath = $defPropPath;
		$this->eiuEntryGuiTypeDef = $eiuEntryGuiTypeDef;
		$this->eiuAnalyst = $eiuAnalyst;
	}
	
	/**
	 * @return DefPropPath
	 */
	function getPath() {
		return $this->defPropPath;
	}
	
	function createCallId() {
		$eiEntryGuiTypeDef = $this->eiuEntryGuiTypeDef->getEiEntryGuiTypeDef();
		
		return new ApiFieldCallId($this->defPropPath, 
				$eiEntryGuiTypeDef->getEiEntry()->getEiMask()->getEiTypePath(),
				$this->eiuAnalyst->getEiuGuiFrame(true)->getViewMode(),
				$eiEntryGuiTypeDef->getEiEntry()->getPid());
	}
}