<?php
namespace nql\bo;

use n2n\reflection\ObjectAdapter;
use n2n\reflection\annotation\AnnoInit;
use n2n\persistence\orm\annotation\AnnoTable;
use n2n\persistence\orm\annotation\AnnoManyToOne;
use n2n\l10n\N2nLocale;

class EventT extends ObjectAdapter {
	private static function _annos(AnnoInit $ai) {
		$ai->c(new AnnoTable('ha_event_t'));
		$ai->p('event', new AnnoManyToOne(Event::getClass()));
	}
	
	private $id;
	private $n2nLocale;
	private $name;
	private $pathPart;
	private $intro;
	private $location;
	private $contentHtml;
	private $externalUrl;
	private $event;
	
	public function getId() {
		return $this->id;
	}
	
	public function setId($id) {
		$this->id = $id;
	}
	
	public function getN2nLocale() {
		return $this->n2nLocale;
	}
	
	public function setN2nLocale(N2nLocale $n2nLocale) {
		$this->n2nLocale = $n2nLocale;
	}
	
	public function getName() {
		return $this->name;
	}
	
	public function setName($name) {
		$this->name = $name;
	}
	
	public function getPathPart() {
		return $this->pathPart;
	}
	
	public function setPathPart($pathPart) {
		$this->pathPart = $pathPart;
	}
	
	public function getIntro() {
		return $this->intro;
	}
	
	public function setIntro($intro) {
		$this->intro = $intro;
	}
	
	public function getLocation() {
		return $this->location;
	}
	
	public function setLocation($location) {
		$this->location = $location;
	}
	
	public function getContentHtml() {
		return $this->contentHtml;
	}
	
	public function setContentHtml($contentHtml) {
		$this->contentHtml = $contentHtml;
	}
	
	public function getExternalUrl() {
		return $this->externalUrl;
	}
	
	public function setExternalUrl($externalUrl) {
		$this->externalUrl = $externalUrl;
	}
	
	public function getEvent() {
		return $this->event;
	}
	
	public function setEvent(Event $event) {
		$this->event = $event;
	}
	
	public function getDateFrom() {
		return $this->event->getDateFrom();
	}
	
	public function getDateTo() {
		return $this->event->getDateTo();
	}
	
	public function getFileImage() {
		return $this->event->getFileImage();
	}
}