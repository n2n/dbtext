<?php
namespace rocket\ei\util\control;

use rocket\si\control\SiCallResponse;
use rocket\ei\util\EiuAnalyst;
use rocket\ei\EiType;
use n2n\l10n\Message;
use n2n\util\uri\Url;
use rocket\ei\manage\veto\EiLifecycleMonitor;
use rocket\ei\manage\EiObject;

class EiuControlResponse {
	private $eiuAnalyst;
	/**
	 * @var SiCallResponse
	 */
	private $siResult;
	/**
	 * @var bool
	 */
	private $noAutoEvents = false;
	
	/**
	 * @var EiObject
	 */
	private $pendingHighlightEiObjects = [];
	
	/**
	 * @param EiuAnalyst $eiuAnalyst
	 */
	function __construct(EiuAnalyst $eiuAnalyst) {
		$this->eiuAnalyst = $eiuAnalyst;
		$this->siResult = new SiCallResponse();
	}
	
	/**
	 * @return \rocket\ei\util\control\EiuControlResponse
	 */
	function redirectBack() {
		$this->siResult->setDirective(SiCallResponse::DIRECTIVE_REDIRECT_BACK);
		
		$eiFrame = $this->eiuAnalyst->getEiFrame(true);
		
		if (null !== ($overviewNavPoint = $eiFrame->getOverviewNavPoint(false))) {
			$this->siResult->setNavPoint($overviewNavPoint);
		}
		
		return $this;
	}
	
	/**
	 * @param Url $url
	 * @return \rocket\ei\util\control\EiuControlResponse
	 */
	function redirectBackOrRef(Url $url) {
		$this->siResult->setDirective(SiCallResponse::DIRECTIVE_REDIRECT_BACK);
		$this->siResult->setRef($url);
		return $this;
	}
	
	/**
	 * @param Url $url
	 * @return \rocket\ei\util\control\EiuControlResponse
	 */
	function redirectBackOrHref(Url $url) {
		$this->siResult->setDirective(SiCallResponse::DIRECTIVE_REDIRECT_BACK);
		$this->siResult->setHref($url);
		return $this;
	}
	
	/**
	 * @param Url $url
	 * @return \rocket\ei\util\control\EiuControlResponse
	 */
	function redirectToRef(Url $url) {
		$this->siResult->setDirective(SiCallResponse::DIRECTIVE_REDIRECT);
		$this->siResult->setRef($url);
		return $this;
	}
	
	/**
	 * @param Url $url
	 * @return \rocket\ei\util\control\EiuControlResponse
	 */
	function redirectToHref(Url $url) {
		$this->siResult->setDirective(SiCallResponse::DIRECTIVE_REDIRECT_BACK);
		$this->siResult->setHref($url);
		return $this;
	}
	
	/**
	 * @param Message|string $message
	 * @return \rocket\ei\util\control\EiuControlResponse
	 */
	function message($message) {
		$this->siResult->addMessage(Message::create($message), 
				$this->eiuAnalyst->getN2nContext(true)->getN2nLocale());
		return $this;
	}
	
// 	/**
// 	 * @param mixed ...$eiTypeArgs
// 	 * @return EiuControlResponse
// 	 */
// 	public function eiTypeChanged(...$eiTypeArgs) {
// 		foreach ($eiTypeArgs as $eiTypeArg) {
// 			$this->groupChanged(self::buildTypeId(EiuAnalyst::buildEiTypeFromEiArg($eiTypeArg)));
// 		}
// 		return $this;
// 	}

	/**
	 * @param mixed ...$eiObjectArgs
	 * @return \rocket\ei\util\control\EiuControlResponse
	 */
	function highlight(...$eiObjectArgs) {
		foreach ($eiObjectArgs as $eiObjectArg) {
			$eiObject = EiuAnalyst::buildEiObjectFromEiArg($eiObjectArg, 'eiObjectArg', null, true);
			
			if (!$eiObject->getEiEntityObj()->hasId()) {
				$this->pendingHighlightEiObjects[] = $eiObject;
				continue;
			}
			
			$this->siResult->addHighlight(
					self::buildCategory($eiObject->getEiEntityObj()->getEiType()), 
					$eiObject->getEiEntityObj()->getPid());
		}
		
		return $this;
	}
	
	/**
	 * @param bool $noAutoEvents
	 * @return EiuControlResponse
	 */
	function noAutoEvents(bool $noAutoEvents = true) {
		$this->noAutoEvents = true;
		return $this;
	}
	
	/**
	 * @param mixed ...$eiObjectArgs
	 * @return EiuControlResponse
	 */
	function entryAdded(...$eiObjectArgs) {
		foreach ($eiObjectArgs as $eiObjectArg) {
			$this->eiObjectMod($eiObjectArg, SiCallResponse::MOD_TYPE_ADDED);
		}
		return $this;
	}
	
	/**
	 * @param mixed ...$eiObjectArgs
	 * @return EiuControlResponse
	 */
	function entryChanged(...$eiObjectArgs) {
		foreach ($eiObjectArgs as $eiObjectArg) {
			$this->eiObjectMod($eiObjectArg, SiCallResponse::MOD_TYPE_CHANGED);
		}
		return $this;
	}
	
	/**
	 * @param mixed ...$eiObjectArgs
	 * @return EiuControlResponse
	 */
	function entryRemoved(...$eiObjectArgs) {
		foreach ($eiObjectArgs as $eiObjectArg) {
			$this->eiObjectMod($eiObjectArg, SiCallResponse::MOD_TYPE_REMOVED);
		}
		return $this;
	}
	
	private function eiObjectMod($eiObjectArg, string $modType) {
		$eiObject = EiuAnalyst::buildEiObjectFromEiArg($eiObjectArg, 'eiObjectArg', null, true);
		
		$category = self::buildCategory($eiObject->getEiEntityObj()->getEiType());
		
		$pid = null;
		if ($eiObject->getEiEntityObj()->hasId()) {
			$pid = $eiObject->getEiEntityObj()->getPid();
		}
		
		$this->siResult->addEvent($eiObject->createSiEntryIdentifier(), $modType);
	}
	
	/**
	 * @param EiType $eiType
	 * @return string
	 */
	private static function buildCategory(EiType $eiType) {
		return $eiType->getSupremeEiType()->getId();
	}
	
	/**
	 * @param EiLifecycleMonitor $elm
	 * @return \rocket\si\control\SiCallResponse
	 */
	function toSiCallResponse(EiLifecycleMonitor $elm) {
		if ($this->noAutoEvents) {
			return $this->siResult;
		}
		
		$taa = $elm->approve();
		
		if (!$taa->isSuccessful()) {
			$this->message(...$taa->getReasonMessages());
			return;
		}
		
		foreach ($this->pendingHighlightEiObjects as $eiObject) {
			$this->highlight($eiObject);
		}
		$this->pendingHighlightEiObjects = [];
		
		foreach ($elm->getUpdateActions() as $action) {
			$this->eiObjectMod($action->getEiObject(), SiCallResponse::EVENT_TYPE_CHANGED);
			$this->highlight($action->getEiObject());
		}
		
		foreach ($elm->getPersistActions() as $action) {
			$this->eiObjectMod($action->getEiObject(), SiCallResponse::EVENT_TYPE_ADDED);
			$this->highlight($action->getEiObject());
		}
		
		foreach ($elm->getRemoveActions() as $action) {
			$this->eiObjectMod($action->getEiObject(), SiCallResponse::EVENT_TYPE_REMOVED);
		}
		
		return $this->siResult;
	}
}