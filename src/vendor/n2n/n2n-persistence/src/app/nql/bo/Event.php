<?php
namespace nql\bo;

use n2n\reflection\ObjectAdapter;
use n2n\persistence\orm\annotation\AnnoTable;
use n2n\reflection\annotation\AnnoInit;
use n2n\persistence\orm\annotation\AnnoOneToMany;
use n2n\persistence\orm\CascadeType;
use n2n\persistence\orm\annotation\AnnoManagedFile;
use n2n\io\managed\File;

class Event extends ObjectAdapter {
	private static function _annos(AnnoInit $ai) {
		$ai->c(new AnnoTable('ha_event'));
		$ai->p('eventTs', new AnnoOneToMany(EventT::getClass(), 'event', CascadeType::ALL, null, true));
		$ai->p('fileImage', new AnnoManagedFile()); 
	}
	
	private $id;
	private $fileImage;
	private $dateFrom;
	private $dateTo;
	private $eventTs;
	
	public function getId() {
		return $this->id;
	}
	
	public function setId($id) {
		$this->id = $id;
	}
	public function getFileImage() {
		return $this->fileImage;
	}
	
	public function setFileImage(File $fileImage) {
		$this->fileImage = $fileImage;
	}
	
	public function getDateFrom() {
		return $this->dateFrom;
	}
	
	public function setDateFrom(\DateTime $dateFrom) {
		$this->dateFrom = $dateFrom;
	}
	
	public function getDateTo() {
		return $this->dateTo;
	}
	
	public function setDateTo(\DateTime $dateTo = null) {
		$this->dateTo = $dateTo;
	}
	
	public function getEventTs() {
		return $this->eventTs;
	}
	
	public function setEventTs($eventTs) {
		$this->eventTs = $eventTs;
	}
}
