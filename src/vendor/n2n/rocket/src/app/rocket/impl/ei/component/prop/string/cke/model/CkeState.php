<?php
namespace rocket\impl\ei\component\prop\string\cke\model;

use n2n\context\RequestScoped;

class CkeState implements RequestScoped {
	private $registeredCkeLinkProviderLookupIds = [];	
	private $registeredCkeCssConfigLookupIds = [];
	
	public function registerCkeCssConfig(CkeCssConfig $ckeCssConfig) {
		$this->registeredCkeCssConfigLookupIds[get_class($ckeCssConfig)] = get_class($ckeCssConfig);
	}
	
	public function registerCkeLinkProvider(CkeLinkProvider $ckeLinkProvider) {
		$this->registeredCkeLinkProviderLookupIds[get_class($ckeLinkProvider)] = get_class($ckeLinkProvider);
	}
	
	public function getRegisteredCkeLinkProviderLookupIds() {
		return $this->registeredCkeLinkProviderLookupIds;
	}

	public function getRegisteredCkeCssConfigLookupIds() {
		return $this->registeredCkeCssConfigLookupIds;
	}
}