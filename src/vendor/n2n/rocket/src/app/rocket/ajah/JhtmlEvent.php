<?php
namespace rocket\ajah;

use rocket\ei\util\EiJhtmlEventInfo;

class JhtmlEvent {

	public static function common() {
		return new JhtmlEventInfo();
	}

	/**
	 * @return \rocket\ei\util\EiJhtmlEventInfo
	 */
	public static function ei() {
		return new EiJhtmlEventInfo();
	}
}