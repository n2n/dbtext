<?php
namespace nql\bo;

use n2n\reflection\annotation\AnnoInit;
use n2n\persistence\orm\annotation\AnnoTable;
use n2n\persistence\orm\annotation\AnnoManyToOne;
use n2n\reflection\ObjectAdapter;

class Member extends ObjectAdapter {
	private static function _annos(AnnoInit $ai) {
		$ai->c(new AnnoTable('nql_member'));
		$ai->p('assignementGroup', new AnnoManyToOne(AssignementGroup::getClass()));
	}
	
	private $id;
	private $assignementGroup;
	
	public function getId() {
		return $this->id;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function getAssignementGroup() {
		return $this->assignementGroup;
	}

	public function setAssignementGroup($assignementGroup) {
		$this->assignementGroup = $assignementGroup;
	}
}