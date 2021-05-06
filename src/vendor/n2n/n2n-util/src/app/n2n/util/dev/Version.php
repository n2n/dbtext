<?php
namespace n2n\util\dev;

use n2n\util\type\ArgUtils;

/**
 * Represets a version according to {@link https://semver.org/}
 */
class Version {
	const NUM_SEPARATOR = '.';
	const STAGE_SEPARATOR = '-';
	
	private $nums;
	private $stage;
	private $stageNums;
	
	/**
	 * 
	 * @param int[] $nums
	 * @param string $stage
	 * @param array $stageNums
	 */
	function __construct(array $nums, string $stage = null, array $stageNums = array()) {
		ArgUtils::valArray($nums, 'int');
		$this->nums = $nums;
		ArgUtils::valArray($stageNums, 'int');
	}
	
	/**
	 * @return int|null
	 */
	function getMajorNum() {
		return $this->nums[0] ?? null;
	}
	
	/**
	 * @return int|null
	 */
	function getMinorNum() {
		return $this->nums[1] ?? null;
	}
	
	/**
	 * @return int|null
	 */
	function getPatchNum() {
		return $this->nums[2] ?? null;
	}
	
	/**
	 * @return int[]
	 */
	function getStageNums() {
		return $this->stageNums;
	}
	
	static function create(string $str) {
		$parts = explode(self::STAGE_SEPARATOR, $str, 2);
		
		$nums = explode(self::NUM_SEPARATOR, $parts[0]);
		if (!isset($parts[1])) {
			return new Version($nums);
		}
		
		$stageParts = explode(self::NUM_SEPARATOR, $parts[1]);
		$stage = array_shift($stageParts);
		
		return new Version($nums, $stage, $stageParts);
		
		
	}
}

