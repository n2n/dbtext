<?php

namespace rocket\spec\result;

use rocket\ei\component\command\EiCommand;
use rocket\ei\EiCommandPath;
use rocket\spec\TypePath;

class EiCommandError {
	private $eiTypePath;
	private $eiCommandPath;
	private $eiCommand;
	private $t;
	
	public function __construct(TypePath $eiTypePath, EiCommandPath $eiCommandPath, \Throwable $t, 
			EiCommand $eiCommand = null) {
		$this->eiTypePath = $eiTypePath;
		$this->eiCommandPath = $eiCommandPath;
		$this->eiCommand = $eiCommand;
		$this->t = $t;
	}
	
	public function getEiCommandPath() {
		return $this->eiCommandPath;
	}
	
	public function getEiCommand() {
		return $this->eiCommand;
	}
	
	public function getEiTypePath() {
		return $this->eiTypePath;
	}
	
	/**
	 * @return \Throwable
	 */
	public function getThrowable() {
		return $this->t;
	}
	
	public static function fromEiCommand(EiCommand $eiCommand, \Throwable $t) {
		$wrapper = $eiCommand->getWrapper();
		return new EiCommandError($wrapper->getEiCommandCollection()->getEiMask()->getEiTypePath(),
				$wrapper->getEiCommandPath(), $t, $eiCommand);
	}
}