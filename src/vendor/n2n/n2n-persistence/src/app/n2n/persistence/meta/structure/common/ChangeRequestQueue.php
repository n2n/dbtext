<?php
/*
 * Copyright (c) 2012-2016, Hofmänner New Media.
 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
 *
 * This file is part of the N2N FRAMEWORK.
 *
 * The N2N FRAMEWORK is free software: you can redistribute it and/or modify it under the terms of
 * the GNU Lesser General Public License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * N2N is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
 * the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details: http://www.gnu.org/licenses/
 *
 * The following people participated in this project:
 *
 * Andreas von Burg.....: Architect, Lead Developer
 * Bert Hofmänner.......: Idea, Frontend UI, Community Leader, Marketing
 * Thomas Günther.......: Developer, Hangar
 */
namespace n2n\persistence\meta\structure\common;

use n2n\persistence\Pdo;

class ChangeRequestQueue {
	
	private $changeRequests;
	
	public function __construct() {
		$this->initialize();
	}
	
	/**
	 * @param Pdo $dbh
	 */
	public function persist(Pdo $dbh) {
//		$dbh->beginTransaction();
// 		try {
			foreach ($this->changeRequests as $changeRequest) {
				$changeRequest->execute($dbh);
			}
// 		} catch (\Throwable $e) {
// 			$dbh->rollBack();
// 			throw $e;
// 		}
// 		$dbh->commit();
		$this->initialize();
	}
	
	/**
	 * Add a changerequest to the changerequest Queue
	 * @param ChangeRequest $changeRequest
	 */
	public function add(ChangeRequest $changeRequest) {
		$this->changeRequests[spl_object_hash($changeRequest)] = $changeRequest;
	}
	
	public function remove(ChangeRequest $changeRequest) {
		unset($this->changeRequests[spl_object_hash($changeRequest)]);
	}
	
	/**
	 * Get All Pending Change Requests
	 * @return ChangeRequest []
	 */
	public function getAll() {
		return $this->changeRequests;
	}
	
	private function initialize() {
		$this->changeRequests = array();
	}
}
