<?php
namespace nql\bo;

use n2n\reflection\ObjectAdapter;
use n2n\reflection\annotation\AnnoInit;
use n2n\persistence\orm\annotation\AnnoTable;
use n2n\persistence\orm\annotation\AnnoManyToOne;

class Comment extends ObjectAdapter {
	private static function _annos(AnnoInit $ai) {
		$ai->c(new AnnoTable('nql_comment'));
		$ai->p('blogArticle', new AnnoManyToOne(BlogArticle::getClass()));
	}
	private $id;
	private $blogArticle;
	private $author;
}