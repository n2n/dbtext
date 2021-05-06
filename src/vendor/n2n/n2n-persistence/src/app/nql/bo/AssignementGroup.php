<?php
namespace nql\bo;

use n2n\reflection\ObjectAdapter;
use n2n\reflection\annotation\AnnoInit;
use n2n\persistence\orm\annotation\AnnoTable;
use n2n\persistence\orm\annotation\AnnoOneToMany;

class AssignementGroup extends ObjectAdapter {
	private static function _annos(AnnoInit $ai) {
		$ai->c(new AnnoTable('nql_assingement_group'));
		$ai->p('members', new AnnoOneToMany(Member::getClass(), 'assignementGroup'));
	}
	
	private $id;
	private $members;
	
	public function getId() {
		return $this->id;
	}

	public function setId($id) {
		$this->id = $id;
	}

	public function getMembers() {
		return $this->members;
	}

	public function setMembers($members) {
		$this->members = $members;
	}
}

