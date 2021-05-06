<?php
namespace rocket\si\meta;

class SiStructureType {
	const SIMPLE_GROUP = 'simple-group';
	const MAIN_GROUP = 'main-group';
// 	const AUTONOMIC_GROUP = 'autonomic-group';
	const LIGHT_GROUP = 'light-group';
	const PANEL = 'panel';
	const ITEM = 'item';
	
	/**
	 * @return string[]
	 */
	static function groups() {
		return array(self::SIMPLE_GROUP, self::MAIN_GROUP,/* self::AUTONOMIC_GROUP,*/
				self::LIGHT_GROUP);
	}
	
	/**
	 * @param string $type
	 * @return boolean
	 */
	static function isGroup(string $type) {
		return in_array($type, self::groups());
	}
	
	/**
	 * @return string[]
	 */
	static function all() {
		return array(self::ITEM, self::SIMPLE_GROUP, self::MAIN_GROUP,/* self::AUTONOMIC_GROUP,*/
				self::LIGHT_GROUP, self::PANEL);
	}
}
