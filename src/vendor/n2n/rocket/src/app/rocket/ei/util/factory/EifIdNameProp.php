<?php
namespace rocket\ei\util\factory;

use rocket\impl\ei\component\prop\adapter\idname\ClosureIdNameProp;

class EifIdNameProp {
	private $callback;
	
	function __construct(\Closure $callback) {
		$this->callback = $callback;
	}
	
	/**
	 * 
	 * @return \rocket\ei\manage\idname\IdNameProp
	 */
	function toIdNameProp() {
		return new ClosureIdNameProp($this->callback);
	}
}
