<?php
// namespace rocket\ei\util;

// use rocket\ei\EiType;
// use rocket\ei\manage\EiObject;
// use n2n\web\ui\SimpleBuildContext;
// use rocket\ajah\JhtmlEventInfo;
// use rocket\ei\manage\veto\EiLifecycleMonitor;

// class EiJhtmlEventInfo extends JhtmlEventInfo {
//     const ATTR_CHANGES_KEY = 'eiMods';
// 	const ATTR_SWAP_CONTROL_HTML_KEY = 'swapControlHtml';
	
	
// 	private $noAutoEvents = false;
// 	private $eventMap = array();
// 	private $swapControl;
	
// 	private function evMapEiType(string $eiTypeId) {
// 	    $this->eventMap[$eiTypeId] = self::MOD_TYPE_CHANGED;
// 	}
	
	
	
// 	/**
// 	 * @param Control $control
// 	 * @return \rocket\ei\util\EiJhtmlEventInfo
// 	 */
// 	public function controlSwaped(Control $control) {
// 		$this->swapControl = $control;
// 		return $this;
// 	}
	
	
	
// 	/**
// 	 * @param EiObject $eiObject
// 	 * @return string
// 	 */
// 	public static function buildItemId(EiObject $eiObject) {
// 		if ($eiObject->isDraft()) {
// 			return 'draft-id-' . $eiObject->getDraft()->getId();
// 		}
		
// 		return 'live-ei-id-' . $eiObject->getEiEntityObj()->getId();
// 	}
	
	
// 	public function toAttrs(): array {
// 		$attrs = parent::toAttrs(); 
		
// 		if ($this->swapControl !== null) {
// 			$attrs[self::ATTR_SWAP_CONTROL_HTML_KEY] = $this->swapControl->createUiComponent()
// 					->build(new SimpleBuildContext());	
// 		}
		
// 		if (!empty($this->eventMap)) {
// 			$attrs[self::ATTR_CHANGES_KEY] = $this->eventMap;
// 		}
		
// 		return $attrs;
// 	}
// }
