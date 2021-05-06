<?php
namespace rocket\ei\manage\security\privilege\data;

use n2n\util\type\ArgUtils;
use rocket\ei\EiCommandPath;
use n2n\util\type\attrs\DataSet;
use rocket\ei\EiPropPath;
use n2n\util\type\attrs\DataSet;

class PrivilegeSetting {
	const ATTR_EXECUTABLE_EI_COMMAND_PATHS_KEY = 'executableEiCommandPaths';
	const ATTR_WRITABLE_EI_PROP_PATHS_KEY = 'writableEiPropPaths';
	
	private $writableEiPropPaths = array();
	private $executableEiCommandPaths = array();
	
	/**
	 * @param EiCommandPath[] $executableEiCommandPaths
	 * @param EiCommandPath[] $writableEiPropPaths
	 */
	function __construct(array $executableEiCommandPaths = array(), array $writableEiPropPaths = null) {
		$this->setExecutableEiCommandPaths($executableEiCommandPaths);
		$this->setWritableEiPropPaths($writableEiPropPaths ?? new DataSet());
	}
	
	/**
	 * @return EiCommandPath[]
	 */
	function getExecutableEiCommandPaths() {
		return $this->executableExecutableEiCommandPaths;
	}
	
	/**
	 * @param EiCommandPath[] $executableEiCommandPaths
	 */
	function setExecutableEiCommandPaths(array $executableEiCommandPaths) {
		ArgUtils::valArray($executableEiCommandPaths, EiCommandPath::class);
		$this->executableEiCommandPaths = $executableEiCommandPaths;
	}
	
	/**
	 * @param EiCommandPath $eiCommandPath
	 * @return boolean
	 */
	public function acceptsEiCommandPath(EiCommandPath $eiCommandPath) {
		foreach ($this->getEiCommandPaths() as $privilegeCommandPath) {
			if ($privilegeCommandPath->startsWith($eiCommandPath)) return true;
		}
		return false;
	}
	
	/**
	 * @return \n2n\util\type\attrs\DataSet
	 */
	function getWritableEiPropPaths() {
		return $this->writableEiPropPaths;
	}
	
	/**
	 * @param EiPropPath $dataSet
	 */
	function setWritableEiPropPaths(array $writableEiPropPaths) {
		ArgUtils::valArray($writableEiPropPaths, EiPropPath::class);
		$this->writableEiPropPaths = $writableEiPropPaths;
	}
	
	/**
	 * @return array
	 */
	function toAttrs() {
		$eiCommandPathAttrs = array();
		foreach ($this->executableEiCommandPaths as $eiCommandPath) {
			$eiCommandPathAttrs[] = (string) $eiCommandPath;
		}
		
		$eiPropPathAttrs = array();
		foreach ($this->writableEiPropPaths as $eiPropPath) {
			$eiPropPathAttrs[] = (string) $eiPropPath;
		}
		
		return array(
				self::ATTR_EXECUTABLE_EI_COMMAND_PATHS_KEY => $eiCommandPathAttrs,
				self::ATTR_WRITABLE_EI_PROP_PATHS_KEY => $eiPropPathAttrs);
	}
	
	/**
	 * @param DataSet $dataSet
	 * @return \rocket\ei\manage\security\privilege\data\PrivilegeSetting
	 */
	static function createFromDataSet(DataSet $ds) {
		$executableEiCommandPaths = [];
		foreach ($ds->optScalarArray(self::ATTR_EXECUTABLE_EI_COMMAND_PATHS_KEY) as $eiCommandPathStr) {
			$executableEiCommandPaths[] = EiCommandPath::create($eiCommandPathStr);
		}
		
		$writableEiPropPaths = [];
		foreach ($ds->optScalarArray(self::ATTR_WRITABLE_EI_PROP_PATHS_KEY) as $eiPropPathStr) {
			$writableEiPropPaths[] = EiPropPath::create($eiPropPathStr);
		}
		
		return new PrivilegeSetting($executableEiCommandPaths, $writableEiPropPaths);
	}
}