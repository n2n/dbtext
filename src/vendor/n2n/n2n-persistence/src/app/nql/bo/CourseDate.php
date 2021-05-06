<?php
namespace nql\bo;

use n2n\reflection\ObjectAdapter;
use n2n\io\managed\File;
use n2n\reflection\annotation\AnnoInit;
use n2n\persistence\orm\annotation\AnnoTable;
use n2n\l10n\N2nLocale;
use n2n\l10n\L10nUtils;
use n2n\persistence\orm\annotation\AnnoManagedFile;

class CourseDate extends ObjectAdapter {
	private static function _annos(AnnoInit $ai) {
		$ai->c(new AnnoTable('course_date'));
		$ai->p('logo', new AnnoManagedFile());
		$ai->p('pdf', new AnnoManagedFile());
	}
	
	const STATUS_FREE = 'free';
	const STATUS_BOOKED_OUT = 'booked-out';
	const STATUS_CANCELED = 'canceled';
	
	private $id;
	private $name;
	private $pathPart;
	private $description;
	private $dateFrom;
	private $dateTo;
	private $status = self::STATUS_FREE;
	private $logo;
	private $sparePlaces;
	private $place;
	private $price;
	private $priceDescription;
	private $pdf;
	
	public function getId() {
		return $this->id;
	}

	public function setId($id) {
		$this->id = $id;
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

	public function getDescription() {
		return $this->description;
	}

	public function setDescription($description) {
		$this->description = $description;
	}
	
	/**
	 * @return \\DateTime
	 */
	public function getDateFrom() {
		return $this->dateFrom;
	}

	public function setDateFrom(\DateTime $dateFrom) {
		$this->dateFrom = $dateFrom;
	}

	/**
	 * @return \\DateTime
	 */
	public function getDateTo() {
		return $this->dateTo;
	}

	public function setDateTo(\DateTime $dateTo = null) {
		$this->dateTo = $dateTo;

	}

	public function getStatus() {
		return $this->status;
	}
	public function setStatus($status) {
		$this->status = $status;
	}

	public function getLogo() {
		return $this->logo;
	}

	public function setLogo(File $logo = null) {
		$this->logo = $logo;
	}

	public function getSparePlaces() {
		return $this->sparePlaces;
	}

	public function setSparePlaces($sparePlaces) {
		$this->sparePlaces = $sparePlaces;
	}

	public function getPlace() {
		return $this->place;
	}

	public function setPlace($place) {
		$this->place = $place;
	}

	public function getPrice() {
		return $this->price;
	}

	public function setPrice($price) {
		$this->price = $price;
	}

	public function getPriceDescription() {
		return $this->priceDescription;
	}

	public function setPriceDescription($priceDescription) {
		$this->priceDescription = $priceDescription;
	}

	public function getPdf() {
		return $this->pdf;
	}

	public function setPdf(File $pdf = null) {
		$this->pdf = $pdf;
	}
	
	public function buildDateString(N2nLocale $n2nLocale) {
		$dateString = L10nUtils::formatDateTime($this->dateFrom, $n2nLocale);
		if (null !== $this->dateTo && $this->dateFrom->format('dmY') !== $this->dateTo->format('dmY')) {
			$dateString .= ' - ' . L10nUtils::formatDateTime($this->dateTo, $n2nLocale);
		}
		
		return $dateString;
	}
}