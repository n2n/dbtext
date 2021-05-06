<?php

namespace rocket\tool\mail\ui;

use n2n\impl\web\ui\view\html\HtmlView;
use rocket\tool\xml\MailItem;
use n2n\web\ui\Raw;

class MailHtmlBuilder {
	
	private $view;
	
	public function __construct(HtmlView $view) {
		$this->view = $view;
	}
	
	public function getMessage(MailItem $mailItem) {
		return new Raw(preg_replace("/((http:\/\/)|(www\.)|(http:\/\/www.))(([^\s<]{4,68})[^\s<]*)/i",'<a href="http://$3$5" target="_blank">$3$5</a>', $mailItem->getMessage()));
	}
	
	public function message(MailItem $mailItem) {
		$this->view->out($this->getMessage($mailItem));
	}
}