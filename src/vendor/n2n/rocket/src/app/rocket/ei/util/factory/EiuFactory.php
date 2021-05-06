<?php
namespace rocket\ei\util\factory;

use rocket\ei\util\privilege\EiuCommandPrivilege;
use rocket\ei\util\control\EiuControlResponse;
use rocket\ei\util\EiuAnalyst;
use n2n\util\type\TypeConstraint;
use rocket\ei\util\Eiu;
use rocket\si\content\SiField;
use rocket\ei\manage\gui\GuiFieldAssembler;
use rocket\ei\component\command\EiCommand;
use rocket\ei\util\control\EiuControlFactory;

class EiuFactory {
	private $eiu;
	private $eiuAnalyst;
	
	/**
	 * @param Eiu $eiu
	 * @param EiuAnalyst $eiuAnalyst
	 */
	function __construct(Eiu $eiu, EiuAnalyst $eiuAnalyst) {
		$this->eiu = $eiu;
		$this->eiuAnalyst = $eiuAnalyst;
	}
	
	/**
	 * @param string $label
	 * @return \rocket\ei\util\privilege\EiuCommandPrivilege
	 */
	function newCommandPrivilege(string $label) {
		return new EiuCommandPrivilege($label);
	}
	
	/**
	 * @return \rocket\ei\util\control\EiuControlResponse
	 */
	function newControlResponse() {
		return new EiuControlResponse($this->eiuAnalyst);
	}
	
	/**
	 * @return \rocket\ei\util\control\EiuControlFactory
	 */
	function controls() {
		return new EiuControlFactory($this->eiuAnalyst);
	}
	
	/**
	 * @param \Closure $callback
	 * @return EifIdNameProp
	 */
	function newIdNameProp(\Closure $callback) {
		return new EifIdNameProp($callback);	
	}
	
	/**
	 * @param TypeConstraint $typeConstraint
	 * @param \Closure $reader
	 * @return EifField
	 */
	function newField(?TypeConstraint $typeConstraint, \Closure $reader) {
		return new EifField($this->eiu, $typeConstraint, $reader);
	}
	
	/**
	 * @param \Closure $closure
	 * @return \rocket\ei\util\factory\EifGuiProp
	 */
	function newGuiProp(\Closure $closure) {
		return new EifGuiProp($closure);
	}
	
	/**
	 * @param \Closure|GuiFieldAssembler $eiGuiCallbackOrAssembler
	 * @return \rocket\ei\util\factory\EifGuiPropSetup
	 */
	function newGuiPropSetup($eiGuiCallbackOrAssembler) {
		return new EifGuiPropSetup($eiGuiCallbackOrAssembler);
	}
	
	/**
	 * @return EifGuiField
	 */
	function newGuiField(SiField $siField) {
		return new EifGuiField($this->eiu, $siField);
	}
}
