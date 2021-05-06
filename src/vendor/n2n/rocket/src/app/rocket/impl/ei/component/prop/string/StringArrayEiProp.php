<?php
namespace rocket\impl\ei\component\prop\string;

use n2n\impl\web\ui\view\html\HtmlView;
use n2n\reflection\property\AccessProxy;
use n2n\util\type\TypeConstraint;
use n2n\web\ui\UiComponent;
use rocket\impl\ei\component\prop\adapter\DraftablePropertyEiPropAdapter;
use rocket\ei\util\Eiu;
use rocket\ei\util\factory\EifGuiField;
use rocket\si\content\impl\SiFields;
use rocket\impl\ei\component\prop\string\conf\StringArrayConfig;
use n2n\util\StringUtils;
use n2n\util\type\ArgUtils;

class StringArrayEiProp extends DraftablePropertyEiPropAdapter {

	private $stringArrayConfig;
	
	function __construct() {
		parent::__construct();
		
		$this->stringArrayConfig = new StringArrayConfig();
	}
	
	public function prepare() {
		$this->getConfigurator()->addAdaption($this->stringArrayConfig);
	}
	
	public function isEntityPropertyRequired(): bool {
		return false;
	}

	public function setObjectPropertyAccessProxy(AccessProxy $objectPropertyAccessProxy = null) {
		parent::setObjectPropertyAccessProxy($objectPropertyAccessProxy);

		$objectPropertyAccessProxy->setConstraint(TypeConstraint::createArrayLike('array', false,
				TypeConstraint::createSimple('scalar')));
	}

	/**
	 * @param HtmlView $view
	 * @param Eiu $eiu
	 * @return UiComponent
	 */
	function createOutEifGuiField(Eiu $eiu): EifGuiField {
		return $eiu->factory()->newGuiField(SiFields::stringOut(implode(', ', $eiu->field()->getValue()))) ;
	}

	public function createInEifGuiField(Eiu $eiu): EifGuiField {
		$values = ArgUtils::toArray($eiu->field()->getValue());
		
		$siField = SiFields::stringArrayIn($values)
				->setMin($this->stringArrayConfig->getMin())
				->setMax($this->stringArrayConfig->getMax())
				->setMessagesCallback(fn () => $eiu->field()->getMessagesAsStrs());
		
		return $eiu->factory()->newGuiField($siField)
				->setSaver(function () use ($siField, $eiu) {
					$eiu->field()->setValue($siField->getValues());
				});
	}

}