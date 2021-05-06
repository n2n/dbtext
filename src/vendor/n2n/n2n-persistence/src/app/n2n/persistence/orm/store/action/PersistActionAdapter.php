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
namespace n2n\persistence\orm\store\action;

use n2n\persistence\orm\model\EntityModel;
use n2n\persistence\orm\store\action\meta\ActionMeta;

abstract class PersistActionAdapter extends EntityActionAdapter implements PersistAction {
	protected $meta;
	protected $onDisableClosures = array();
	
	public function __construct(ActionQueue $actionQueue, EntityModel $entityModel, $id, 
			$entity, ActionMeta $meta) {
		parent::__construct($actionQueue, $entityModel, $id, $entity);
		$this->meta = $meta;
	}
	
	public function hasId() {
		return $this->id !== null;
	}

 	public function isInitialized() {
 		return true;
 	}
	
	public function getMeta() {
		return $this->meta;
	}
}
