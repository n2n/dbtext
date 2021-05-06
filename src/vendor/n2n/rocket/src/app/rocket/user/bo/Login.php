<?php
/*
 * Copyright (c) 2012-2016, Hofmänner New Media.
 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
 *
 * This file is part of the n2n module ROCKET.
 *
 * ROCKET is free software: you can redistribute it and/or modify it under the terms of the
 * GNU Lesser General Public License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * ROCKET is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
 * the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details: http://www.gnu.org/licenses/
 *
 * The following people participated in this project:
 *
 * Andreas von Burg...........:	Architect, Lead Developer, Concept
 * Bert Hofmänner.............: Idea, Frontend UI, Design, Marketing, Concept
 * Thomas Günther.............: Developer, Frontend UI, Rocket Capability for Hangar
 */
namespace rocket\user\bo;

use n2n\reflection\ObjectAdapter;
use n2n\reflection\annotation\AnnoInit;
use n2n\persistence\orm\annotation\AnnoTable;

class Login extends ObjectAdapter {
	private static function _annos(AnnoInit $ai) {
		$ai->c(new AnnoTable('rocket_login'));
	}
	
	private $id;
	private $nick;
	private $wrongPassword;
	private $power;
	private $successfull;
	private $ip;
	private $dateTime;
	/**
	 * @return int $id
	 */
	public function getId() {
		return $this->id;
	}
	/**
	 * @param int $id
	 */
	public function setId($id) {
		$this->id = $id;
	}
	/**
	 * @return string $nick
	 */
	public function getNick() {
		return $this->nick;
	}
	/**
	 * @param string $nick
	 */
	public function setNick($nick) {
		$this->nick = $nick;
	}
	/**
	 * @return string $password
	 */
	public function getWrongPassword() {
		return $this->wrongPassword;
	}
	/**
	 * @param string $password
	 */
	public function setWrongPassword($wrongPassword) {
		$this->wrongPassword = $wrongPassword;
	}
	/**
	 * @return string
	 */
	public function getPower() {
		return $this->power;
	}
	/**
	 * @param string $power
	 */
	public function setPower($power) {
		$this->power = $power;
	}
	/**
	 * @return bool $successfull
	 */
	public function getSuccessfull() {
		return $this->successfull;
	}
	/**
	 * @param bool $successfull
	 */
	public function setSuccessfull($successfull) {
		$this->successfull = $successfull;
	}
	/**
	 * @return string $ip
	 */
	public function getIp() {
		return $this->ip;
	}
	/**
	 * @param string $ip
	 */
	public function setIp($ip) {
		$this->ip = $ip;
	}
	/**
	 * @return \DateTime $datetime
	 */
	public function getDateTime() {
		return $this->dateTime;
	}
	/**
	 * @param \DateTime $datetime
	 */
	public function setDatetime(\DateTime $dateTime) {
		$this->dateTime = $dateTime;
	}
}
