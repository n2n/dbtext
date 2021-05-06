<?php
namespace rocket\impl\ei\component\command\iframe\controller;

use n2n\web\http\controller\ControllerAdapter;
use n2n\web\http\PageNotFoundException;
use n2n\web\ui\Raw;
use rocket\ei\util\EiuCtrl;
use rocket\impl\ei\component\command\iframe\config\IframeConfig;
use rocket\ei\component\InvalidEiComponentConfigurationException;
use n2n\util\magic\MagicObjectUnavailableException;
use n2n\web\http\controller\Controller;

class IframeController extends ControllerAdapter {
	/**
	 * @var IframeConfig
	 */
	private $iframeConfig;

	/**
	 * IframeController constructor.
	 * @param IframeConfig $iframeConfig
	 */
	public function __construct(IframeConfig $iframeConfig) {
		$this->iframeConfig = $iframeConfig;
	}

	function index(int $pid = null) {
		$eiuCtrl = EiuCtrl::from($this->cu());

		$this->verifyPid($pid);

		if (null !== ($url = $this->iframeConfig->getUrl())) {
			$url = $url->ext($pid);
			$eiuCtrl->forwardUrlIframeZone($url, $this->iframeConfig->getWindowTitle());
		} else if (null !== ($controllerLookupId = $this->iframeConfig->getControllerLookupId())) {
			$eiuCtrl->forwardUrlIframeZone($this->getUrlToController(['src', $pid]),
					$this->iframeConfig->getWindowTitle());
		} else if (null !== ($viewName = $this->iframeConfig->getViewName())) {
			$uiComponent = $eiuCtrl->eiu()->createView($viewName, [$this->iframeConfig->getEntryIdParamName() => $pid]);
			$eiuCtrl->forwardIframeZone($uiComponent, $this->iframeConfig->isUseTemplate(),
					$this->iframeConfig->getWindowTitle());
		} else {
			$eiuCtrl->forwardIframeZone(new Raw($this->iframeConfig->getSrcDoc()), $this->iframeConfig->isUseTemplate(),
					$this->iframeConfig->getWindowTitle());
		}
	}

 	function doSrc(array $params = []) {
 		$eiuCtrl = EiuCtrl::from($this->cu());
 		$controller = null;
 		try {
 			$controller = $eiuCtrl->eiu()->lookup($this->iframeConfig->getControllerLookupId());
 		} catch (MagicObjectUnavailableException $e) {
 			throw new InvalidEiComponentConfigurationException($this->eiCommand . ' invalid configured.', 0, $e);
 		}
		
 		if (!($controller instanceof Controller)) {
 			throw new InvalidEiComponentConfigurationException($this->eiCommand . ' invalid configured. '
 					. get_class($controller) . ' does not implement ' . Controller::class, 0, $e);
 		}
		
 		$this->delegate($controller);
 	}

	private function verifyPid(?int $pid) {
		if ($this->iframeConfig->isEntryCommand()) {
			if ($pid !== null) {
				// throws PageNotFound if pid invalid
				EiuCtrl::from($this->cu())->lookupObject($pid);
			} else {
				throw new PageNotFoundException();
			}
		} elseif ($pid !== null) {
			throw new PageNotFoundException();
		}
	}


}
