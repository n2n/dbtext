<?php
namespace rocket\ei\util\entry;

use rocket\ei\manage\security\EiEntryAccess;
use rocket\ei\EiPropPath;
use rocket\ei\EiCommandPath;
use rocket\ei\component\command\EiCommand;

class EiuEntryAccess {
	private $eiEntryAccess;
	private $eiuEntry;
	
	function __construct(EiEntryAccess $eiEntryAccess, EiuEntry $eiuEntry) {
		$this->eiEntryAccess = $eiEntryAccess;
		$this->eiuEntry = $eiuEntry;
	}
	
	/**
	 * @param string|EiCommandPath|EiCommand $eiCommandPath
	 * @return boolean
	 */
	function isExecutableBy($eiCommandPath) {
		return $this->eiEntryAccess->isEiCommandExecutable(EiCommandPath::create($eiCommandPath));
	}
	
	function isPropWritable($eiPropPath) {
		return $this->eiEntryAccess->isEiPropWritable(EiPropPath::create($eiPropPath));
	}
}

