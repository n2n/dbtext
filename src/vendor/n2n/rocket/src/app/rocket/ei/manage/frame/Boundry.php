<?php
namespace rocket\ei\manage\frame;

use rocket\ei\manage\entry\EiEntryConstraint;

class Boundry {
	/**
	 * Used by relations for example.
	 * @var int
	 */
	const TYPE_MANAGE = 1;
	/**
	 * Used by filter in overview for example.
	 * @var int
	 */
	const TYPE_TMP_FILTER = 2;
	/**
	 * Used by sort in overview for example.
	 * @var int
	 */
	const TYPE_TMP_SORT = 4;
	/**
	 * @var int
	 */
	const TYPE_SECURITY = 8;
	/**
	 * Used for filters directly assigned to an {@see \rocket\ei\mask\EiMask} (e. g. in specs.json or by hangar).
	 * @var int
	 */
	const TYPE_HARD_FILTER = 16;
	/**
	 * Used for sorts directly assigned to an {@see \rocket\ei\mask\EiMask} (e. g. in specs.json or by hangar).
	 * @var int
	 */
	const TYPE_HARD_SORT = 32;
	
	const TMP_TYPES = 6;
	const HARD_TYPES = 48;
	const ALL_TYPES = 63;
	const NON_SECURITY_TYPES = 55;
	
	/**
	 * @var CriteriaFactory|null
	 */
	private $criteriaFactory = null;
	/**
	 * @var CriteriaConstraint[][]
	 */
	private $criteriaConstraints = array();
	/**
	 * @var EiEntryConstraint[][]
	 */
	private $eiEntryConstraints = array();
	
	/**
	 * @param CriteriaFactory|null $criteriaFactory
	 */
	function setCriteriaFactory(?CriteriaFactory $criteriaFactory) {
		$this->criteriaFactory = $criteriaFactory;
	}
	
	/**
	 * @return \rocket\ei\manage\frame\CriteriaFactory|null
	 */
	function getCriteriaFactory() {
		return $this->criteriaFactory;
	}
	
	/**
	 * @param array $arr
	 * @param int $type
	 * @param object $obj
	 */
	private function add(array &$arr, int $type, $obj) {
		if (!isset($arr[$type])) {
			$arr[$type] = array();
		}
		
		$arr[$type][] = $obj;
	}
	
	/**
	 * @param array $arr
	 * @param int $ignoredTypes
	 * @return object[]
	 */
	private function filter(array &$arr, int $ignoredTypes) {
		$filteredObjs = array();
		foreach ($arr as $type => $objs) {
			if ($ignoredTypes != 0 && ($ignoredTypes & $type)) {
				continue;
			}
			
			array_push($filteredObjs, ...$objs);
		}
		return $filteredObjs;
	}
	
	/**
	 * @param int $type
	 * @param CriteriaConstraint $criteriaConstraint
	 */
	function addCriteriaConstraint(int $type, CriteriaConstraint $criteriaConstraint) {
		$this->add($this->criteriaConstraints, $type, $criteriaConstraint);
	}
	
	/**
	 * @param int $types
	 * @return CriteriaConstraint[]
	 */
	function filterCriteriaConstraints(int $ignoredTypes) {
		return $this->filter($this->criteriaConstraints, $ignoredTypes);
	}

	/**
	 * @param int $type
	 * @param EiEntryConstraint $eiEntryConstraint
	 */
	function addEiEntryConstraint(int $type, EiEntryConstraint $eiEntryConstraint) {
		$this->add($this->eiEntryConstraints, $type, $eiEntryConstraint);
	}
	
	/**
	 * @param int $types
	 * @return EiEntryConstraint[]
	 */
	function filterEiEntryConstraints(int $ignoredTypes) {
		return $this->filter($this->eiEntryConstraints, $ignoredTypes);
	}
	
	/**
	 * @return int[]
	 */
	static function getTypes() {
		return [self::TYPE_MANAGE,
				self::TYPE_TMP_FILTER,
				self::TYPE_TMP_SORT,
				self::TYPE_SECURITY,
				self::TYPE_HARD_FILTER,
				self::TYPE_HARD_SORT];
		
	}
}