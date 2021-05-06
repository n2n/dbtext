<?php
namespace rocket\ei\util\sort;

use n2n\web\dispatch\Dispatchable;
use n2n\reflection\annotation\AnnoInit;
use n2n\web\dispatch\annotation\AnnoDispObject;
use n2n\impl\web\ui\view\html\HtmlView;
use n2n\web\ui\BuildContext;
use n2n\web\ui\ViewFactory;
use n2n\util\type\CastUtils;
use n2n\web\dispatch\map\PropertyPath;
use n2n\web\ui\UiComponent;
use rocket\ei\util\EiuAnalyst;
use rocket\ei\manage\critmod\sort\SortDefinition;
use rocket\ei\manage\critmod\sort\SortSettingGroup;
use rocket\ei\util\sort\form\SortForm;

class EiuSortForm implements Dispatchable, UiComponent {
	private static function _annos(AnnoInit $ai) {
		$ai->p('sortForm', new AnnoDispObject());
	}
	
	/**
	 * @var SortDefinition
	 */
	private $sortDefinition;
	/**
	 * @var EiuAnalyst
	 */
	private $eiuAnalyst;
	/**
	 * @var SortForm
	 */
	private $sortForm;
	
	function __construct(SortDefinition $sortDefinition, ?SortSettingGroup $sortSetting, EiuAnalyst $eiuAnalyst) {
		$this->sortDefinition = $sortDefinition;
		$this->eiuAnalyst = $eiuAnalyst;
		
		$this->writeSetting($sortSetting ?? new SortSettingGroup(), $sortDefinition);
	}
	
	/**
	 * @return \rocket\ei\manage\critmod\sort\SortDefinition
	 */
	function getSortDefinition() {
		return $this->sortDefinition;
	}
	
	/**
	 * @param SortSettingGroup $sortSetting
	 * @return EiuSortForm
	 */
	function writeSetting(SortSettingGroup $sortSetting) {
		$this->sortForm = new SortForm($sortSetting, $this->sortDefinition);
		return $this;
	}
	
	/**
	 * @return \rocket\ei\manage\critmod\sort\SortSettingGroup
	 */
	function readSetting() {
		return $this->sortForm->buildSortSettingGroup();
	}
	
	function getSortForm() {
		return $this->sortForm;
	}
	
	function setSortForm(SortForm $sortForm) {
		$this->sortForm = $sortForm;
	}
	
	private function _validation() {
	}
	
	/**
	 * @return EiuSortForm
	 */
	function clear() {
		$this->sortForm->clear();
		return $this;
	}
	
	/**
	 * @param PropertyPath|null $propertyPath
	 * @return EiuSortForm
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
			return $contextView->getImport('\rocket\ei\util\sort\view\eiuSortForm.html',
					array('eiuSortForm' => $this));
		}
		
		$viewFactory = $this->eiuAnalyst->getN2nContext(true)->lookup(ViewFactory::class);
		CastUtils::assertTrue($viewFactory instanceof ViewFactory);
		
		return $viewFactory->create('rocket\ei\util\sort\view\eiuSortForm.html', array('eiuSortForm' => $this));
	}
	
	public function build(BuildContext $buildContext): string {
		$view = $this->createView($buildContext->getView());
		if (!$view->isInitialized()) {
			$view->initialize(null, $buildContext);
		}
		return $view->getContents();
	}

}