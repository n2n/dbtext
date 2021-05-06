<?php
namespace rocket\impl\ei\component\prop\bool\conf;

use n2n\util\type\attrs\LenientAttributeReader;
use n2n\impl\web\dispatch\mag\model\group\TogglerMag;
use n2n\impl\web\dispatch\mag\model\MultiSelectMag;
use n2n\util\type\TypeConstraint;
use rocket\ei\manage\DefPropPath;
use rocket\ei\component\prop\indepenent\PropertyAssignation;
use n2n\util\StringUtils;
use rocket\ei\component\prop\indepenent\CompatibilityLevel;
use n2n\web\dispatch\mag\MagCollection;
use n2n\util\type\attrs\DataSet;
use rocket\ei\util\Eiu;
use rocket\impl\ei\component\prop\adapter\config\PropConfigAdaption;
use n2n\util\type\ArgUtils;

class BooleanConfig extends PropConfigAdaption {
	const ATTR_BIND_GUI_PROPS_KEY = 'associatedGuiProps';
	const ATTR_ON_ASSOCIATED_GUI_PROP_KEY = 'onAssociatedGuiProps';
	const ATTR_OFF_ASSOCIATED_GUI_PROP_KEY = 'offAssociatedGuiProps';
	
	private static $booleanNeedles = ['Available', 'Enabled'];
	
	private $onAssociatedDefPropPaths = array();
	private $offAssociatedDefPropPaths = array();
	
	/**
	 * @param DefPropPath[] $onAssociatedDefPropPaths
	 */
	public function setOnAssociatedDefPropPaths(array $onAssociatedDefPropPaths) {
		ArgUtils::valArray($onAssociatedDefPropPaths, DefPropPath::class);
		$this->onAssociatedDefPropPaths = $onAssociatedDefPropPaths;
	}
	
	/**
	 * @return DefPropPath[]
	 */
	public function getOnAssociatedDefPropPaths() {
		return $this->onAssociatedDefPropPaths;
	}
	
	/**
	 * @param DefPropPath[] $offAssociatedDefPropPaths
	 */
	public function setOffAssociatedDefPropPaths(array $offAssociatedDefPropPaths) {
		ArgUtils::valArray($offAssociatedDefPropPaths, DefPropPath::class);
		$this->offAssociatedDefPropPaths = $offAssociatedDefPropPaths;
		
	}
	
	/**
	 * @return DefPropPath[]
	 */
	public function getOffAssociatedDefPropPaths() {
		return $this->offAssociatedDefPropPaths;
	}
	
	
	public function testCompatibility(PropertyAssignation $propertyAssignation): ?int {
		$propertyName = $this->requirePropertyName();
		foreach (self::$booleanNeedles as $booleanNeedle) {
			if (StringUtils::endsWith($booleanNeedle, $propertyName)) {
				return CompatibilityLevel::COMMON;
			}
		}
		
		return null;
	}
	
	
	public function mag(Eiu $eiu, DataSet $dataSet, MagCollection $magCollection) {
		$lar = new LenientAttributeReader($dataSet);
		
		$assoicatedGuiPropOptions = $eiu->mask()->engine()->getGuiPropOptions();
		
		$onDefPropPathStrs = $lar->getScalarArray(self::ATTR_ON_ASSOCIATED_GUI_PROP_KEY);
		$offDefPropPathStrs = $lar->getScalarArray(self::ATTR_OFF_ASSOCIATED_GUI_PROP_KEY);
		
		$eMag = new TogglerMag('Bind GuiProps to value', !empty($onDefPropPathStrs) || !empty($offDefPropPathStrs));
		
		$magCollection->addMag(self::ATTR_BIND_GUI_PROPS_KEY, $eMag);
		$eMag->setOnAssociatedMagWrappers(array(
				$magCollection->addMag(self::ATTR_ON_ASSOCIATED_GUI_PROP_KEY, 
						new MultiSelectMag('Associated Gui Fields when on', $assoicatedGuiPropOptions, $onDefPropPathStrs)),
				$magCollection->addMag(self::ATTR_OFF_ASSOCIATED_GUI_PROP_KEY, 
						new MultiSelectMag('Associated Gui Fields when off', $assoicatedGuiPropOptions, $offDefPropPathStrs))));
	}
	
	function save(Eiu $eiu, MagCollection $magCollection, DataSet $dataSet) {
		if (!$magCollection->readValue(self::ATTR_BIND_GUI_PROPS_KEY)) {
			return;
		}
		
		$onDefPropPathStrs = $magCollection->readValue(self::ATTR_ON_ASSOCIATED_GUI_PROP_KEY);
		$offDefPropPathsStrs = $magCollection->readValue(self::ATTR_OFF_ASSOCIATED_GUI_PROP_KEY);
		
		$dataSet->set(self::ATTR_ON_ASSOCIATED_GUI_PROP_KEY, $onDefPropPathStrs);
		$dataSet->set(self::ATTR_OFF_ASSOCIATED_GUI_PROP_KEY, $offDefPropPathsStrs);
	}
	
	function setup(Eiu $eiu, DataSet $dataSet) {
		if ($dataSet->contains(self::ATTR_ON_ASSOCIATED_GUI_PROP_KEY)) {
			$onDefPropPathStrs = $dataSet->getArray(self::ATTR_ON_ASSOCIATED_GUI_PROP_KEY, false, array(), 
					TypeConstraint::createSimple('scalar'));
			$onDefPropPaths = array();
			foreach ($onDefPropPathStrs as $eiPropPathStr) {
				$onDefPropPaths[] = DefPropPath::create($eiPropPathStr);
			}
			
			$this->setOnAssociatedDefPropPaths($onDefPropPaths);
		}
		
		if ($dataSet->contains(self::ATTR_OFF_ASSOCIATED_GUI_PROP_KEY)) {
			$offDefPropPathStrs = $dataSet->getArray(self::ATTR_OFF_ASSOCIATED_GUI_PROP_KEY, false, array(),
					TypeConstraint::createSimple('scalar'));
			$offDefPropPaths = array();
			foreach ($offDefPropPathStrs as $eiPropPathStr) {
				$offDefPropPaths[] = DefPropPath::create($eiPropPathStr);
			}
			
			$this->setOffAssociatedDefPropPaths($offDefPropPaths);
		}
	}
}