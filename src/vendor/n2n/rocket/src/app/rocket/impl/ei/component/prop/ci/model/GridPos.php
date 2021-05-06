<?php
namespace rocket\impl\ei\component\prop\ci\model;

use rocket\si\content\impl\relation\SiGridPos;

class GridPos {
	private $colStart;
	private $colEnd;
	private $rowStart;
	private $rowEnd;
	
	/**
	 * @param int $colStart
	 * @param int $colEnd
	 * @param int $rowStart
	 * @param int $rowEnd
	 */
	public function __construct(int $colStart, ?int $colEnd, int $rowStart, ?int $rowEnd) {
		$this->colStart = $colStart;
		$this->colEnd = $colEnd ?? $colStart + 1;
		$this->rowStart = $rowStart;
		$this->rowEnd = $rowEnd ?? $rowStart + 1;
		
		if ($this->colStart < 1) {
			$this->colStart = 1;
		}
		
		if ($this->rowStart < 1) {
			$this->rowStart = 1;
		}
		
		if ($this->colStart >= $this->colEnd) {
			$this->colEnd = $this->colStart + 1;
		}
		
		if ($this->rowStart > $this->rowEnd) {
			$this->rowEnd = $this->rowStart + 1;
		}
	}
	
	/**
	 * @return int
	 */
	public function getColStart() {
		return $this->colStart;
	}
	
	/**
	 * @param int $colStart
	 */
	public function setColStart(int $colStart) {
		$this->colStart = $colStart;
	}
	
	/**
	 * @return int
	 */
	public function getColEnd() {
		return $this->colEnd;
	}
	
	/**
	 * @param int $colEnd
	 */
	public function setColEnd(int $colEnd) {
		$this->colEnd = $colEnd;
	}
	
	/**
	 * @return int
	 */
	public function getRowStart() {
		return $this->rowStart;
	}
	
	/**
	 * @param int $rowStart
	 */
	public function setRowStart(int $rowStart) {
		$this->rowStart = $rowStart;
	}
	
	/**
	 * @return int
	 */
	public function getRowEnd() {
		return $this->rowEnd;
	}
	
	/**
	 * @param int $rowEnd
	 */
	public function setRowEnd(int $rowEnd) {
		$this->rowEnd = $rowEnd;
	}
	
	/**
	 * @return \rocket\si\content\impl\relation\SiGridPos
	 */
	function toSiGridPos() {
		return new SiGridPos($this->colStart, $this->colEnd, $this->rowStart, $this->rowEnd);
	}
}