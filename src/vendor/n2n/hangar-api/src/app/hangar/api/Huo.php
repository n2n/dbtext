<?php
namespace hangar\api;

use n2n\context\Lookupable;
use n2n\core\container\N2nContext;
use n2n\core\module\Module;

class Huo implements Lookupable {
	
	private $n2nContext;
	private $huoContext;
	private $module;
	
	public function __construct(... $args) {
		foreach ($args as $arg) {
			if ($arg instanceof HuoContext) {
				$this->huoContext = $arg;
				continue;
			}
			
			if ($arg instanceof N2nContext) {
				$this->n2nContext = $arg;
				continue;
			}
			
			if ($arg instanceof Module) {
				$this->module = $arg;
				continue;
			}
		}
	}
	
	public function _init($n2nContext) {
		$this->n2nContext = $n2nContext;
	}
	
	/**
	 * @return \n2n\core\module\Module
	 */
	public function getModule() {
		return $this->module;
	}
	
	/**
	 * @return HuoContext
	 */
	public function getHuoContext() {
		return $this->huoContext;
	}
	
	/**
	 * @return \n2n\core\container\N2nContext
	 */
	public function getN2nContext() {
		return $this->n2nContext;
	}
	
	public function getAppN2nContext() {
		return $this->huoContext->getN2nContext();
	}
	
	/**
	 * @param string|\ReflectionClass $lookupId
	 * @return mixed
	 */
	public function lookup($lookupId, bool $required = true) {
		return $this->n2nContext->lookup($lookupId, $required);
	}
}