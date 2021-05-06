<?php
namespace rocket\ei\util\factory;

use rocket\ei\util\Eiu;
use rocket\impl\ei\component\prop\adapter\gui\ClosureGuiField;
use rocket\si\content\SiField;

class EifGuiField {
	private $eiu;
	private $siField;
	private $saverClosure;
	
	
	/**
	 * @param Eiu $eiu
	 * @param SiField $siField
	 */
	function __construct(Eiu $eiu, SiField $siField) {
		$this->eiu = $eiu;
		$this->siField = $siField;
	}
	
	/**
	 * @param \Closure $closure
	 * @throws \InvalidArgumentException
	 * @return \rocket\ei\util\factory\EifGuiField
	 */
	function setSaver(?\Closure $closure) {
		if ($closure !== null && $this->siField->isReadOnly()) {
			throw new \InvalidArgumentException('Saver disallowed for read only SiField.');
		}
		
		$this->saverClosure = $closure;
		return $this;
	}
	
	/**
	 * @return \rocket\si\content\SiField
	 */
	function toGuiField() {
		return new ClosureGuiField($this->eiu, $this->siField, $this->saverClosure);
	}
}