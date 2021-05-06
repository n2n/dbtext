<?php
namespace nql\bo;

use n2n\persistence\orm\annotation\AnnoTable;
use n2n\reflection\annotation\AnnoInit;

class Enemy {
	private static function _annos(AnnoInit $ai) {
		$ai->c(new AnnoTable('nql_enemy'));
	}
	private $id;
	private $name;
}