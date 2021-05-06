<?php
namespace rocket\ei\util\filter;

use rocket\ei\manage\critmod\filter\FilterDefinition;
use n2n\web\dispatch\Dispatchable;
use n2n\reflection\annotation\AnnoInit;
use n2n\web\dispatch\annotation\AnnoDispObject;
use rocket\ei\util\filter\form\FilterGroupForm;
use rocket\ei\manage\critmod\filter\data\FilterSettingGroup;
use n2n\impl\web\ui\view\html\HtmlView;
use n2n\web\ui\BuildContext;
use n2n\web\ui\ViewFactory;
use n2n\util\type\CastUtils;
use n2n\web\dispatch\map\PropertyPath;
use n2n\web\ui\UiComponent;
use rocket\ei\util\EiuAnalyst;
use rocket\ei\util\filter\controller\FilterJhtmlHook;

class EiuFilterForm implements Dispatchable, UiComponent {
	private static function _annos(AnnoInit $ai) {
		$ai->p('filterGroupForm', new AnnoDispObject());
	}
	
	/**
	 * @var FilterDefinition
	 */
	private $filterDefinition;
	/**
	 * @var FilterJhtmlHook
	 */
	private $filterJhtmlHook;
	/**
	 * @var EiuAnalyst
	 */
	private $eiuAnalyst;
	/**
	 * @var FilterGroupForm
	 */
	private $filterGroupForm;
	
	function __construct(FilterDefinition $filterDefinition, FilterJhtmlHook $filterJhtmlHook, 
			?FilterSettingGroup $rootGroup, EiuAnalyst $eiuAnalyst) {
		$this->filterDefinition = $filterDefinition;
		$this->filterJhtmlHook = $filterJhtmlHook;
		$this->eiuAnalyst = $eiuAnalyst;
		
		$this->setSettings($rootGroup ?? new FilterSettingGroup(), $filterDefinition);
	}
	
	/**
	 * @return \rocket\ei\manage\critmod\filter\FilterDefinition
	 */
	function getFilterDefinition() {
		return $this->filterDefinition;
	}
	
	/**
	 * @return \rocket\ei\util\filter\controller\FilterJhtmlHook
	 */
	function getFilterJhtmlHook() {
		return $this->filterJhtmlHook;
	}
	
	/**
	 * @param FilterSettingGroup $rootGroup
	 * @return EiuFilterForm
	 */
	function setSettings(FilterSettingGroup $rootGroup) {
		$this->filterGroupForm = new FilterGroupForm($rootGroup, $this->filterDefinition);
		return $this;
	}
	
	/**
	 * @return \rocket\ei\manage\critmod\filter\data\FilterSettingGroup
	 */
	function getSettings() {
		return $this->filterGroupForm->buildFilterSettingGroup();
	}
	
	function getFilterGroupForm() {
		return $this->filterGroupForm;
	}
	
	function setFilterGroupForm(FilterGroupForm $filterGroupForm) {
		$this->filterGroupForm = $filterGroupForm;
	}
	
	private function _validation() {
	}
	
	/**
	 * @return EiuFilterForm
	 */
	function clear() {
		$this->filterGroupForm->clear();
		return $this;
	}
	
	/**
	 * @param PropertyPath|null $propertyPath
	 * @return EiuFilterForm
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
			return $contextView->getImport('\rocket\ei\util\filter\view\eiuFilterForm.html',
					array('eiuFilterForm' => $this));
		}
		
		$viewFactory = $this->eiuAnalyst->getN2nContext(true)->lookup(ViewFactory::class);
		CastUtils::assertTrue($viewFactory instanceof ViewFactory);
		
		return $viewFactory->create('rocket\ei\util\filter\view\eiuFilterForm.html', array('eiuFilterForm' => $this));
	}
	
	public function build(BuildContext $buildContext): string {
		$view = $this->createView($buildContext->getView());
		if (!$view->isInitialized()) {
			$view->initialize(null, $buildContext);
		}
		return $view->getContents();
	}

}