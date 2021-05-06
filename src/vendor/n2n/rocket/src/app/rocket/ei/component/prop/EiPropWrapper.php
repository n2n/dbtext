<?php
namespace rocket\ei\component\prop;

use rocket\ei\EiPropPath;

class EiPropWrapper {
	private $eiPropPath;
	private $eiProp;
	private $eiPropCollection;
	
	/**
	 * @param EiPropPath $eiPropPath
	 * @param EiProp $eiProp
	 */
	public function __construct(EiPropPath $eiPropPath, EiProp $eiProp, EiPropCollection $eiPropCollection) {
		$this->eiPropPath = $eiPropPath;
		$this->eiProp = $eiProp;
		$this->eiPropCollection = $eiPropCollection;
		
		$eiProp->setWrapper($this);
	}
	
	/**
	 * @return \rocket\ei\EiPropPath
	 */
	public function getEiPropPath() {
		return $this->eiPropPath;
	}
	
	/**
	 * @return EiProp
	 */
	public function getEiProp() {
		return $this->eiProp;
	}
	
	/**
	 * @return \rocket\ei\component\prop\EiPropCollection
	 */
	public function getEiPropCollection() {
		return $this->eiPropCollection;
	}
}