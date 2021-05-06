<?php
namespace rocket\spec\result;

use rocket\ei\component\prop\EiProp;
use rocket\ei\EiPropPath;
use rocket\spec\TypePath;
use rocket\impl\ei\component\prop\adapter\EiPropAdapter;

class EiPropError {
	private $eiTypePath;
	private $eiPropPath;
	private $eiProp;
	private $t;
	
	public function __construct(TypePath $eiTypePath, EiPropPath $eiPropPath, \Throwable $t, EiProp $eiProp = null) {
		$this->eiTypePath = $eiTypePath;
		$this->eiPropPath = $eiPropPath;
		$this->eiProp = $eiProp;
		$this->t = $t;
	}
	
	public function getEiPropPath() {
		return $this->eiPropPath;
	}
	
	public function getEiProp() {
		return $this->eiProp;
	}
	
	public function getEiTypePath() {
		return $this->eiTypePath;
	}
	
	/**
	 * @return \Throwable
	 */
	public function getThrowable() {
		return $this->t;
	}
	
	public static function fromEiProp(EiPropAdapter $eiProp, \Throwable $t) {
		$wrapper = $eiProp->getWrapper();
		return new EiPropError($wrapper->getEiPropCollection()->getEiMask()->getEiTypePath(), 
				$wrapper->getEiPropPath(), $t, $eiProp);
	}
}