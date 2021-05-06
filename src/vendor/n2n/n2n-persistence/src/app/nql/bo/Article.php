<?php
namespace nql\bo;

use n2n\reflection\ObjectAdapter;
use n2n\reflection\annotation\AnnoInit;
use n2n\persistence\orm\annotation\AnnoTable;
use n2n\persistence\orm\annotation\AnnoDateTime;

class Article extends ObjectAdapter {
	private static function _annos(AnnoInit $ai) {
		$ai->c(new AnnoTable('nql_article'));
		$ai->p('birthday', new AnnoDateTime());
	}
	
	private $id;
	private $title;
	private $active;
	private $birthday;
}