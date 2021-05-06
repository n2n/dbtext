<?php
namespace rocket\ei\util\privilege;

use n2n\web\dispatch\Dispatchable;
use rocket\ei\manage\security\privilege\PrivilegeDefinition;
use n2n\impl\web\dispatch\mag\model\MagForm;
use n2n\web\dispatch\annotation\AnnoDispObject;
use n2n\reflection\annotation\AnnoInit;
use rocket\ei\util\EiuAnalyst;
use n2n\web\dispatch\annotation\AnnoDispProperties;
use rocket\ei\manage\security\privilege\data\PrivilegeSetting;
use n2n\web\ui\UiComponent;
use n2n\impl\web\ui\view\html\HtmlView;
use n2n\web\ui\ViewFactory;
use n2n\util\type\CastUtils;
use n2n\web\ui\BuildContext;
use n2n\web\dispatch\map\PropertyPath;
use rocket\ei\EiCommandPath;
use n2n\web\dispatch\map\bind\BindingDefinition;
use n2n\impl\web\dispatch\map\val\ValEnum;

class EiuPrivilegeForm implements Dispatchable, UiComponent {
	private static function _annos(AnnoInit $ai) {
		$ai->c(new AnnoDispProperties('eiCommandPathStrs'));
		$ai->p('eiPropMagForm', new AnnoDispObject());
	}

	/**
	 * @var PrivilegeDefinition
	 */
	private $privilegeDefinition;
	/**
	 * @var EiuAnalyst
	 */
	private $eiuAnalyst;
	/**
	 * @var PrivilegeSetting
	 */
	private $privilegeSetting;
	/**
	 * @var PropertyPath
	 */
	private $contextPropertyPath;
	
	/**
	 * @var MagForm
	 */
	private $eiPropMagForm;
	
	/**
	 * @param PrivilegeDefinition $privilegeDefinition
	 * @param EiuAnalyst $eiuAnalyst
	 */
	function __construct(PrivilegeDefinition $privilegeDefinition, ?PrivilegeSetting $privilegeSetting, EiuAnalyst $eiuAnalyst) {
		$this->privilegeDefinition = $privilegeDefinition;
		$this->eiuAnalyst = $eiuAnalyst;
		
		$this->setSetting($privilegeSetting ?? new PrivilegeSetting());
	}
	
	/**
	 * @return \rocket\ei\manage\security\privilege\PrivilegeDefinition
	 */
	function getPrivilegeDefinition() {
		return $this->privilegeDefinition;
	}
	
	/**
	 * @return \rocket\ei\manage\security\privilege\data\PrivilegeSetting
	 */
	function getSetting() {
		return $this->privilegeSetting;
	}
	
	/**
	 * @param PrivilegeSetting $privilegeSetting
	 */
	function setSetting(PrivilegeSetting $privilegeSetting) {
		$this->privilegeSetting = $privilegeSetting;
		$this->eiPropMagForm = new MagForm($this->privilegeDefinition->createEiPropPrivilegeMagCollection(
				$privilegeSetting->getEiPropDataSet()));
	}
	
	/**
	 * @return string[]
	 */
	function getEiCommandPathStrs() {
		$eiCommandPathStrs = [];
		foreach ($this->privilegeSetting->getEiCommandPaths() as $key => $eiCommandPath) {
			$eiCommandPathStrs[$key] = (string) $eiCommandPath;
		}
		return array_combine($eiCommandPathStrs, $eiCommandPathStrs);
	}
	
	/**
	 * @param string[] $eiCommandPathStrs
	 */
	function setEiCommandPathStrs(array $eiCommandPathStrs) {
		$eiCommandPaths = array();
		foreach ($eiCommandPathStrs as $eiCommandPathStr) {
			$eiCommandPaths[] = EiCommandPath::create($eiCommandPathStr);	
		}
		
		$this->privilegeSetting->setEiCommandPaths($eiCommandPaths);
	}
	
	/**
	 * @return \n2n\impl\web\dispatch\mag\model\MagForm
	 */
	function getEiPropMagForm() {
		return $this->eiPropMagForm;
	}
	
	/**
	 * @param MagForm $magForm
	 */
	function setEiPropMagForm(MagForm $magForm) {
		$this->eiPropMagForm = $magForm;
		
		$this->privilegeSetting->setEiPropDataSet(
				$this->privilegeDefinition->buildEiPropPrivilegeDataSet(
						$magForm->getMagCollection()));
	}
	
	private function buildPrivileges(array &$privileges, array $eiCommandPrivileges, EiCommandPath $baseEiCommandPath)  {
		foreach ($eiCommandPrivileges as $commandPathStr => $eiCommandPrivilege) {
			$commandPath = $baseEiCommandPath->ext($commandPathStr);
			
			$privileges[] = (string) $commandPath;
			
			$this->buildPrivileges($privileges, $eiCommandPrivilege->getSubEiCommandPrivileges(), $commandPath);
		}
	}
	
	private function _validation(BindingDefinition $bd) {
		$commandPathStrs = array();
		$this->buildPrivileges($commandPathStrs, $this->privilegeDefinition->getEiCommandPrivileges(),
				new EiCommandPath(array()));
		$bd->val('eiCommandPathStrs', new ValEnum($commandPathStrs));
	}
	
	/**
	 * @param PropertyPath|null $propertyPath
	 * @return EiuPrivilegeForm
	 */
	public function setContextPropertyPath(?PropertyPath $propertyPath) {
		$this->contextPropertyPath = $propertyPath;
		return $this;
	}
	
	/**
	 * @return \n2n\web\dispatch\map\PropertyPath
	 */
	public function getContextPropertyPath() {
		return $this->contextPropertyPath;
	}
	
	/**
	 * @param HtmlView $contextView
	 * @return \n2n\impl\web\ui\view\html\HtmlView
	 */
	function createView(HtmlView $contextView = null) {
		if ($contextView !== null) {
			return $contextView->getImport('\rocket\ei\util\privilege\view\eiuPrivilegeForm.html',
					array('eiuPrivilegeForm' => $this));
		}
		
		$viewFactory = $this->eiuAnalyst->getN2nContext(true)->lookup(ViewFactory::class);
		CastUtils::assertTrue($viewFactory instanceof ViewFactory);
		
		return $viewFactory->create('rocket\ei\util\privilege\view\eiuPrivilegeForm.html', 
				array('eiuPrivilegeForm' => $this));
	}
	
	public function build(BuildContext $buildContext): string {
		$view = $this->createView($buildContext->getView());
		if (!$view->isInitialized()) {
			$view->initialize(null, $buildContext);
		}
		return $view->getContents();
	}
	
}