<?php
namespace rocket\impl\ei\component\prop\iframe;

use n2n\impl\web\dispatch\mag\model\BoolMag;
use n2n\impl\web\dispatch\mag\model\StringMag;
use n2n\persistence\meta\structure\Column;
use n2n\util\type\attrs\DataSet;
use n2n\util\uri\Url;
use n2n\web\dispatch\mag\MagCollection;
use rocket\ei\util\Eiu;
use rocket\impl\ei\component\prop\adapter\config\PropConfigAdaption;

class IframeConfig extends PropConfigAdaption {
	const ATTR_URL_KEY = 'url';
	const ATTR_SRC_DOC_KEY = 'srcDoc';
	const ATTR_CONTROLLER_LOOKUP_ID = 'controllerLookupId';
	const ATTR_USE_TEMPLATE_KEY = 'useTemplate';

	private $url;
	private $srcDoc;
	private $controllerLookupId;
	private $useTemplate = true;

	function setup(Eiu $eiu, DataSet $dataSet) {
		$this->setUrl(Url::build($dataSet->optString(self::ATTR_URL_KEY), true));
		$this->setSrcDoc($dataSet->optString(self::ATTR_SRC_DOC_KEY));
		$this->setControllerLookupId($dataSet->optString(self::ATTR_CONTROLLER_LOOKUP_ID));
		$this->setUseTemplate($dataSet->optBool(self::ATTR_USE_TEMPLATE_KEY, true));
	}

	function autoAttributes(Eiu $eiu, DataSet $dataSet, Column $column = null) {
		$dataSet->set(self::ATTR_USE_TEMPLATE_KEY, true);
	}

	function mag(Eiu $eiu, DataSet $dataSet, MagCollection $magCollection) {
		$magCollection->addMag(self::ATTR_URL_KEY, new StringMag('URL',
			$dataSet->optString(self::ATTR_URL_KEY, $this->getUrl())));

		$magCollection->addMag(self::ATTR_SRC_DOC_KEY, new StringMag('Source Document',
				$dataSet->optString(self::ATTR_SRC_DOC_KEY, $this->getSrcDoc())));

		$magCollection->addMag(self::ATTR_CONTROLLER_LOOKUP_ID, new StringMag('Controller Lookup Id',
			$dataSet->optString(self::ATTR_URL_KEY, $this->getControllerLookupId())));

		$magCollection->addMag(self::ATTR_USE_TEMPLATE_KEY, new BoolMag('Use Template',
				$dataSet->optBool(self::ATTR_USE_TEMPLATE_KEY, $this->isUseTemplate())));
	}

	function save(Eiu $eiu, MagCollection $magCollection, DataSet $dataSet) {
		$urlMag = $magCollection->getMagByPropertyName(self::ATTR_URL_KEY);
		$srcDocMag = $magCollection->getMagByPropertyName(self::ATTR_SRC_DOC_KEY);
		$useTemplateMag = $magCollection->getMagByPropertyName(self::ATTR_USE_TEMPLATE_KEY);

		$dataSet->set(self::ATTR_URL_KEY, $urlMag->getValue());
		$dataSet->set(self::ATTR_SRC_DOC_KEY, $srcDocMag->getValue());
		$dataSet->set(self::ATTR_USE_TEMPLATE_KEY, $useTemplateMag->getValue());
	}

	/**
	 * @return Url
	 */
	public function getUrl() {
		return $this->url;
	}

	/**
	 * @param Url $url
	 */
	public function setUrl(?Url $url): void {
		$this->url = $url;
	}

	/**
	 * @return string
	 */
	public function getSrcDoc() {
		return $this->srcDoc;
	}

	/**
	 * @param string $srcDoc
	 */
	public function setSrcDoc($srcDoc) {
		$this->srcDoc = $srcDoc;
	}

	/**
	 * @return boolean
	 */
	public function isUseTemplate() {
		return $this->useTemplate;
	}

	/**
	 * @param boolean $useTemplate
	 */
	public function setUseTemplate($useTemplate) {
		$this->useTemplate = $useTemplate;
	}

	/**
	 * @return string|null
	 */
	public function getControllerLookupId() {
		return $this->controllerLookupId;
	}

	/**
	 * @param string|null $controllerLookupId
	 */
	public function setControllerLookupId(string $controllerLookupId = null) {
		$this->controllerLookupId = $controllerLookupId;
	}
}
