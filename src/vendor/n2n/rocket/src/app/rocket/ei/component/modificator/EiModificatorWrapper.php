<?php
namespace rocket\ei\component\modificator;

use rocket\ei\EiModificatorPath;

class EiModificatorWrapper {
	private $eiModificatorPath;
	private $eiModificator;
	private $eiModificatorCollection;
	
	/**
	 * @param EiModificatorPath $eiModificatorPath
	 * @param EiModificator $eiModificator
	 */
	public function __construct(EiModificatorPath $eiModificatorPath, EiModificator $eiModificator, 
			EiModificatorCollection $eiModificatorCollection) {
		$this->eiModificatorPath = $eiModificatorPath;
		$this->eiModificator = $eiModificator;
		$this->eiModificatorCollection = $eiModificatorCollection;
		
		$eiModificator->setWrapper($this);
	}
	
	/**
	 * @return \rocket\ei\EiModificatorPath
	 */
	public function getEiModificatorPath() {
		return $this->eiModificatorPath;
	}
	
	/**
	 * @return EiModificator
	 */
	public function getEiModificator() {
		return $this->eiModificator;
	}
	
	/**
	 * @return \rocket\ei\component\modificator\\EiModificatorCollection
	 */
	public function getEiModificatorCollection() {
		return $this->eiModificatorCollection;
	}
}