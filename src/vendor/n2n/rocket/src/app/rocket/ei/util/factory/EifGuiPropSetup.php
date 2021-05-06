<?php
namespace rocket\ei\util\factory;

use rocket\si\meta\SiStructureType;
use n2n\util\type\ArgUtils;
use rocket\ei\manage\gui\DisplayDefinition;
use rocket\ei\manage\gui\GuiFieldAssembler;
use rocket\ei\manage\gui\field\GuiField;
use n2n\reflection\magic\MagicMethodInvoker;
use n2n\util\type\TypeConstraints;
use rocket\ei\util\Eiu;
use rocket\ei\manage\gui\GuiPropSetup;
use rocket\ei\manage\gui\SimpleGuiPropSetup;

class EifGuiPropSetup {
	private $guiFieldCallbackOrAssembler;
	private $siStructureType = SiStructureType::ITEM;
	private $defaultDisplayed = true;
	private $overwriteLabel = null;
	private $overwriteHelpText = null;
	
	/**
	 * @param \Closure|GuiFieldAssembler $guiFieldCallbackOrAssembler
	 */
	function __construct($guiFieldCallbackOrAssembler) {
		ArgUtils::valType($guiFieldCallbackOrAssembler, ['Closure', GuiFieldAssembler::class]);
		$this->guiFieldCallbackOrAssembler = $guiFieldCallbackOrAssembler;
	}

	/**
	 * @param string $siStructureType
	 * @return \rocket\ei\util\factory\EifGuiPropSetup
	 */
	function setSiStructureType(string $siStructureType) {
		ArgUtils::valEnum($siStructureType, SiStructureType::all());
		$this->siStructureType = $siStructureType;
		return $this;
	}
	
	/**
	 * @return string
	 */
	function getSiStructureType() {
		return $this->siStructureType;
	}
	
	/**
	 * @param bool $defaultDisplayed
	 * @return \rocket\ei\util\factory\EifGuiPropSetup
	 */
	function setDefaultDisplayed(bool $defaultDisplayed) {
		$this->defaultDisplayed = $defaultDisplayed;
		return $this;
	}
	
	/**
	 * @return bool
	 */
	function isDefaultDisplayed() {
		return $this->defaultDisplayed;
	}
	
	/**
	 * @param string|null $overwriteLabel
	 * @return \rocket\ei\util\factory\EifGuiPropSetup
	 */
	function setOverwriteLabel(?string $overwriteLabel) {
		$this->overwriteLabel = $overwriteLabel;
		return $this;
	}
	
	/**
	 * @return string|null
	 */
	function getOverwriteLabel() {
		return $this->overwriteLabel;
	}
	
// 	/**
// 	 * @param \Closure|null
// 	 */
// 	function setGuiFieldFactory(?\Closure $closure) {
// 		$this->closure = $closure;
// 	}
	
// 	/**
// 	 * @return \Closure|null
// 	 */
// 	function getGuiFieldFactory() {
// 		return $this->closure;
// 	}
	
	/**
	 * @return GuiPropSetup
	 */
	function toGuiPropSetup() {
		$displayDefinition = new DisplayDefinition($this->siStructureType, $this->defaultDisplayed,
				$this->overwriteLabel, $this->overwriteHelpText);
		
		$guiFieldAssembler = null;
		if ($this->guiFieldCallbackOrAssembler instanceof GuiFieldAssembler) {
			$guiFieldAssembler = $this->guiFieldCallbackOrAssembler;
		} else if ($this->guiFieldCallbackOrAssembler instanceof \Closure) {
			$guiFieldAssembler = $this->createAssemblerFromClosure($this->guiFieldCallbackOrAssembler);
		} 
		
		return new SimpleGuiPropSetup($guiFieldAssembler, $displayDefinition, []);
	}
	
	/**
	 * @param \Closure $guiFieldClosure
	 * @return GuiFieldAssembler
	 */
	private function createAssemblerFromClosure($guiFieldClosure) {
		return new class($guiFieldClosure) implements GuiFieldAssembler {
			private $guiFieldClosure;
			
			function __construct($guiFieldClosure) {
				$this->guiFieldClosure = $guiFieldClosure;
			}
			
			function buildGuiField(Eiu $eiu, bool $readOnly): ?GuiField {
				$mmi = new MagicMethodInvoker($eiu->getN2nContext());
				$mmi->setClassParamObject(Eiu::class, $eiu);
				$mmi->setParamValue('readOnly', $readOnly);
				$mmi->setReturnTypeConstraint(TypeConstraints::type(GuiField::class, true));
				return $mmi->invoke(null, $this->guiFieldClosure);
			}
		};
	}
}