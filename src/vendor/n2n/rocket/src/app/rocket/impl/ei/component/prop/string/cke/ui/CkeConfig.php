<?php
namespace rocket\impl\ei\component\prop\string\cke\ui;

class CkeConfig {
	private $mode;
	private $tableEnabled;
	private $bbcodeEnabled;

	const MODE_SIMPLE = 'simple';
	const MODE_NORMAL = 'normal';
	const MODE_ADVANCED = 'advanced';

	public function __construct(string $mode, bool $tablesEnabled, bool $bbcodeEnabled) {
		$this->mode = $mode;
		$this->tableEnabled = $tablesEnabled;
		$this->bbcodeEnabled = $bbcodeEnabled;
	}
	
	public function getMode() {
		return $this->mode;
	}
	
	public function isTablesEnabled() {
		return $this->tableEnabled;
	}
	
	public function isBbcodeEnabled() {
		return $this->bbcodeEnabled;
	}
	
	public static function createDefault() {
		return new CkeConfig(self::MODE_NORMAL, false, false);
	}

	static function getModes() {
		return [self::MODE_SIMPLE, self::MODE_NORMAL, self::MODE_ADVANCED];
	}
}
