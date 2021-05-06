<?php
namespace nql\bo;

use n2n\reflection\ObjectAdapter;
use n2n\reflection\annotation\AnnoInit;
use n2n\persistence\orm\annotation\AnnoTable;
use n2n\persistence\orm\annotation\AnnoOneToMany;
use n2n\persistence\orm\annotation\AnnoOneToOne;

class BlogArticle extends ObjectAdapter {
	private static function _annos(AnnoInit $ai) {
		$ai->c(new AnnoTable('nql_blog_article'));
		$ai->p('comments', new AnnoOneToMany(Comment::getClass(), 'blogArticle'));
		$ai->p('latestComment', new AnnoOneToOne(Comment::getClass()));
	}
	
	private $id;
	private $title;
	private $comments;
	private $latestComment;
}