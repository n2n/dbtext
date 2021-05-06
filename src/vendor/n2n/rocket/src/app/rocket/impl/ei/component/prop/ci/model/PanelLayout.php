<?php
namespace rocket\impl\ei\component\prop\ci\model;

use rocket\si\content\impl\relation\SiPanel;
use rocket\si\content\impl\relation\SiGridPos;

class PanelLayout {
	/**
	 * @var SiPanel[]
	 */
	private $siGridPoses = [];
	
	public function __construct() {	
	}
	
	/**
	 * @param PanelDeclaration[] $panelDeclarations
	 */
	function assignConfigs(array $panelDeclarations) {
		$numGridCols = 0;
		$numGridRows = 0;
		
		foreach ($panelDeclarations as $panelDeclaration) {
			$gridPos = $panelDeclaration->getGridPos();
			
			if ($gridPos === null) continue;
			
			$colEnd = $gridPos->getColEnd();
			if ($numGridCols < $colEnd) {
				$numGridCols = $colEnd;
			}
			
			$rowEnd = $gridPos->getRowEnd();
			if ($numGridRows < $rowEnd) {
				$numGridRows = $rowEnd;
			}
		}
		
		$this->siGridPoses = [];
		foreach ($panelDeclarations as $panelDeclaration) {
			if (($gridPos = $panelDeclaration->getGridPos()) !== null) {
				$this->siGridPoses[$panelDeclaration->getName()] = $gridPos->toSiGridPos();
				continue;
			}
			
			$this->siGridPoses[$panelDeclaration->getName()] = new SiGridPos(1, $numGridCols,
					++$numGridRows, $numGridRows);
		}
	}
	
	/**
	 * @return bool
	 */
	public function hasGrid() {
		return $this->numGridCols > 0 || $this->numGridRows > 0;
	}
	
	public function getNumGridCols() {
		return $this->numGridCols;
	}
	
	public function getNumGridRows() {
		return $this->numGridCols;
	}
	
	/**
	 * @param string $panelName
	 * @return SiGridPos|null
	 */
	function getSiGridPos(string $panelName) {
		return $this->siGridPoses[$panelName] ?? null;
	}
}

