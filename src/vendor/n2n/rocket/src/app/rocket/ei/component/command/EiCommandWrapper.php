<?php
namespace rocket\ei\component\command;

use rocket\ei\EiCommandPath;

class EiCommandWrapper {
	private $eiCommandPath;
	private $eiCommand;
	private $eiCommandCollection;
	
	/**
	 * @param EiCommandPath $eiCommandPath
	 * @param EiCommand $eiCommand
	 */
	public function __construct(EiCommandPath $eiCommandPath, EiCommand $eiCommand, EiCommandCollection $eiCommandCollection) {
		$this->eiCommandPath = $eiCommandPath;
		$this->eiCommand = $eiCommand;
		$this->eiCommandCollection = $eiCommandCollection;
		
		$eiCommand->setWrapper($this);
	}
	
	/**
	 * @return \rocket\ei\EiCommandPath
	 */
	public function getEiCommandPath() {
		return $this->eiCommandPath;
	}
	
	/**
	 * @return EiCommand
	 */
	public function getEiCommand() {
		return $this->eiCommand;
	}
	
	/**
	 * @return \rocket\ei\component\command\EiCommandCollection
	 */
	public function getEiCommandCollection() {
		return $this->eiCommandCollection;
	}
}