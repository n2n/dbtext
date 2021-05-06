<?php
namespace rocket\ajah;

use n2n\l10n\Message;
use n2n\l10n\MessageContainer;

class JhtmlEventInfo {
	const ATTR_MESSAGES_KEY = 'messages';
	const ATTR_SEVERITY_KEY = 'severity';
	const ATTR_TEXT_KEY = 'text';
	
	private $resfreshMod;
	private $attrs = array();
	
	/**
	 * @param Message $message
	 * @return \rocket\ajah\JhtmlEventInfo
	 */
	function message(Message ...$messages) {
		if (!isset($this->attrs[self::ATTR_MESSAGES_KEY])) {
			$this->attrs[self::ATTR_MESSAGES_KEY] = array();
		}
		
		foreach ($messages as $message) {
			$this->attrs[self::ATTR_MESSAGES_KEY][] = array(
					self::ATTR_TEXT_KEY => $message->__toString(),
					self::ATTR_SEVERITY_KEY => $message->getSeverity());
		}
		
		return $this;
	}

// 	public function groupChanged(string $groupId) {
// 		$this->eventMap[$groupId] = RocketJhtmlResponse::MOD_TYPE_CHANGED;
// 	}

// 	/**
// 	 * @param string $typeId
// 	 * @param string $entryId
// 	 * @return \rocket\ajah\JhtmlEventInfo
// 	 */
// 	public function itemChanged(string $typeId, string $entryId) {
// 		$this->item($typeId, $entryId, RocketJhtmlResponse::MOD_TYPE_CHANGED);
// 		return $this;
// 	}

// 	/**
// 	 * @param string $typeId
// 	 * @param string $entryId
// 	 * @return \rocket\ajah\JhtmlEventInfo
// 	 */
// 	public function itemRemoved(string $typeId, string $entryId) {
// 		$this->item($typeId, $entryId, RocketJhtmlResponse::MOD_TYPE_REMOVED);
// 		return $this;
// 	}

// 	/**
// 	 * @param string $typeId
// 	 * @param string $entryId
// 	 * @param string $modType
// 	 */
// 	public function item(string $typeId, string $entryId, string $modType) {
// 		if (!isset($this->eventMap[$typeId])) {
// 			$this->eventMap[$typeId] = array();
// 		} else if ($this->eventMap[$typeId] == RocketJhtmlResponse::MOD_TYPE_CHANGED) {
// 			return;
// 		}

// 		$this->eventMap[$typeId][$entryId] = $modType;
// 		return $this;
// 	}

	public function introduceMessageContainer(MessageContainer $mc) {
		if (isset($this->attrs[self::ATTR_MESSAGES_KEY])) return;
		
		foreach ($mc->getAll() as $message) {
			$this->message($message);
		}
	}
	
	public function toAttrs(): array {
		return $this->attrs;
	}
}
