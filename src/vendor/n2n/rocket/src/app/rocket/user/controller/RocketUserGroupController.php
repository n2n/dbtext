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
namespace rocket\user\controller;

use n2n\web\http\controller\ControllerAdapter;
use n2n\l10n\DynamicTextCollection;
use rocket\core\model\Breadcrumb;
use rocket\user\model\RocketUserGroupListModel;
use rocket\core\model\Rocket;
use rocket\user\model\RocketUserGroupForm;
use rocket\core\model\RocketState;
use rocket\user\model\RocketUserDao;
use n2n\l10n\MessageContainer;
use n2n\web\http\PageNotFoundException;
use rocket\user\bo\RocketUserGroup;
use rocket\user\model\GroupGrantsViewModel;
use rocket\spec\UnknownTypeException;
use rocket\ei\UnknownEiTypeExtensionException;
use rocket\user\bo\EiGrant;
use rocket\user\model\EiGrantForm;
use n2n\web\http\controller\impl\ScrRegistry;
use rocket\ei\EiEngine;
use rocket\spec\TypePath;
use rocket\ei\util\Eiu;
use rocket\ajah\RocketJhtmlResponse;

class RocketUserGroupController extends ControllerAdapter {
	private $rocketState;
	private $userDao;
	private $rocket;
	private $dtc;
	
	private function _init(RocketState $rocketState, RocketUserDao $userDao, Rocket $rocket, DynamicTextCollection $dtc) {
		$this->rocketState = $rocketState;
		$this->userDao = $userDao;
		$this->rocket = $rocket;
		$this->dtc = $dtc;
	}
	
	public function index(Rocket $rocket) {
		$this->applyBreadcrumbs();
		
		$this->forward('..\view\groupOverview.html', array(
				'userGroupOverviewModel' => new RocketUserGroupListModel(
						$this->userDao->getRocketUserGroups(), $rocket->getSpec())));
	}
	
	public function doAdd(Rocket $rocket, MessageContainer $messageContainer) {
		$this->beginTransaction();
		
		$userGroupForm = new RocketUserGroupForm(new RocketUserGroup(), $rocket->getLayout(), $rocket->getSpec(), $this->getN2nContext());
		if ($this->dispatch($userGroupForm, 'save')) {
			$this->userDao->saveRocketUserGroup($userGroupForm->getRocketUserGroup());
			$this->commit();
			
			$messageContainer->addInfoCode('user_group_added_info',
					array('group' => $userGroupForm->getRocketUserGroup()->getName()));
			$this->redirectToController();
			return;
		}
		$this->commit();
		
		$this->applyBreadcrumbs($userGroupForm);
		$this->forward('..\view\groupEdit.html', array('userGroupForm' => $userGroupForm));
	}
	
	public function doEdit($userGroupId, Rocket $rocket, MessageContainer $messageContainer) {
		$this->beginTransaction();
		$userGroup = $this->userDao->getRocketUserGroupById($userGroupId);
		if ($userGroup === null) {
			$this->commit();
			throw new PageNotFoundException();
		}
		
		$userGroupForm = new RocketUserGroupForm($userGroup, $rocket->getLayout(), $rocket->getSpec(), 
				$this->getN2nContext());
		if ($this->dispatch($userGroupForm, 'save')) {
			$this->commit();
			$messageContainer->addInfoCode('user_group_edited_info',
					array('group' => $userGroupForm->getRocketUserGroup()->getName()));
			$this->redirectToController();
			return;	
		}
		
		$this->commit();
		
		$this->applyBreadcrumbs($userGroupForm);
		$this->forward('..\view\groupEdit.html', array('userGroupForm' => $userGroupForm));
	}
	
	public function doDelete($userGroupId, MessageContainer $messageContainer) {
		$this->beginTransaction();
		
		if (null !== ($userGroup = $this->userDao->getRocketUserGroupById($userGroupId))) {
			$this->userDao->removeRocketUserGroup($userGroup);

			$messageContainer->addInfoCode('user_group_removed_info',
					array('group' => $userGroup->getName()));
		}
		
		$this->commit();
		$this->redirectToController();
	}
	
	public function doGrants($rocketUserGroupId) {
		$this->beginTransaction();
		
		$userGroup = $this->userDao->getRocketUserGroupById($rocketUserGroupId);
		if ($userGroup === null) {
			$this->commit();
			throw new PageNotFoundException();
		}
		
		$spec = $this->rocket->getSpec();
		$groupGrantViewModel = new GroupGrantsViewModel($userGroup, $spec->getEiTypes(), 
				$spec->getCustomTypes());
		
		$this->commit();
		
		$this->applyBreadcrumbs();
		$this->applyGrantsBc($userGroup);
		
		$this->forward('..\view\groupGrants.html', array('groupGrantsViewModel' => $groupGrantViewModel));
	}
	
	private function applyGrantsBc(RocketUserGroup $userGroup) {
		$this->rocketState->addBreadcrumb(new Breadcrumb(
				$this->getControllerPath()->ext('grants', $userGroup->getId())->toUrl(),
				$this->dtc->t('user_group_grants_of_txt', ['user_group' => $userGroup->getName()])));
	}
	
	public function doFullyEiGrant($userGroupId, $eiTypePathStr, Rocket $rocket) {
		$eiTypePath = null;
		try {
			$eiTypePath = TypePath::create($eiTypePathStr);
		} catch (\InvalidArgumentException $e) {
			throw new PageNotFoundException(null, null, $e);
		}
		
		$eiType = null;
		try {
			$eiType = $rocket->getSpec()->getEiTypeById($eiTypePath->getTypeId());
		} catch (UnknownTypeException $e) {
			throw new PageNotFoundException(null, null, $e);
		}
		
		$eiTypeExtensionId = $eiTypePath->getEiTypeExtensionId();
		if ($eiTypeExtensionId !== null && !$eiType->getEiTypeExtensionCollection()->containsId($eiTypeExtensionId)) {
			throw new PageNotFoundException();
		}
		
		$this->beginTransaction();
		
		$userGroup = $this->userDao->getRocketUserGroupById($userGroupId);
		if ($userGroup === null) {
			$this->commit();
			throw new PageNotFoundException();
		}
		
		$this->send(RocketJhtmlResponse::redirectToReferer($this->getControllerPath()->ext(array('grants', $userGroupId))));
		
		if (null !== ($eiGrant = $userGroup->getEiGrantByEiTypePath($eiTypePath))) {
			$eiGrant->setFull(true);
			$this->commit();
			return;
		}
		
		$eiGrant = new EiGrant();
		$eiGrant->setEiTypePath($eiTypePath);
		$eiGrant->setFull(true);
		$eiGrant->setRocketUserGroup($userGroup);
		$userGroup->getEiGrants()->append($eiGrant);
		
		$this->commit();
	}
	
	public function doRemoveEiGrant($userGroupId, $eiTypePathStr) {
		$this->beginTransaction();
	
		$userGroup = $this->userDao->getRocketUserGroupById($userGroupId);
		if ($userGroup === null) {
			throw new PageNotFoundException();
		}
		
		$eiTypePath = null;
		try {
			$eiTypePath = TypePath::create($eiTypePathStr);
		} catch (\InvalidArgumentException $e) {
			throw new PageNotFoundException(null, null, $e);
		}
	
		$eiGrants = $userGroup->getEiGrants();
		foreach ($eiGrants as $key => $eiGrant) {
			if ($eiGrant->getEiTypePath()->equals($eiTypePath)) {
				$eiGrants->offsetUnset($key);
				break;
			}
		}
	
		$this->commit();
		$this->send(RocketJhtmlResponse::redirectToReferer($this->getControllerPath()->ext(array('grants', $userGroupId))));
	}
	
	/**
	 * @param string $eiTypePath
	 * @throws PageNotFoundException
	 * @return EiEngine
	 */
	private function lookupEiEngine(TypePath $eiTypePath) {
		$eiType = null;
		try {
			$eiType = $this->rocket->getSpec()->getEiTypeById($eiTypePath->getTypeId());
		} catch (UnknownTypeException $e) {
			throw new PageNotFoundException(null, 0, $e);
		}
		
		$eiTypeExtensionId = $eiTypePath->getEiTypeExtensionId();
		
		if ($eiTypeExtensionId === null) {
			return $eiType->getEiMask()->getEiEngine();
		}
		
		try {
			return $eiType->getEiTypeExtensionCollection()->getById($eiTypeExtensionId)->getEiMask()->getEiEngine();
		} catch (UnknownEiTypeExtensionException $e) {
			throw new PageNotFoundException(null, 0, $e);
		}
	}
	
	/**
	 * @param int $rocketUserGroupId
	 * @param string $eiTypeId
	 * @param string $eiMaskId
	 * @param ScrRegistry $scrRegistry
	 * @throws PageNotFoundException
	 */
	public function doRestrictEiGrant($rocketUserGroupId, string $eiTypePathStr, Eiu $eiu) {
		$eiTypePath = null;
		try {
			$eiTypePath = TypePath::create($eiTypePathStr);
		} catch (\InvalidArgumentException $e) {
			throw new PageNotFoundException(null, null, $e);
		}
		
		$eiuEngine = null;
		try {
			$eiuEngine = $eiu->context()->engine($eiTypePath);
		} catch (UnknownTypeException $e) {
			throw new PageNotFoundException(null, null, $e);
		}
		
		$this->beginTransaction();
		
		$rocketUserGroup = $this->userDao->getRocketUserGroupById($rocketUserGroupId);
		if ($rocketUserGroup === null) {
			throw new PageNotFoundException();
		}
		
		$eiGrant = $rocketUserGroup->getEiGrantByEiTypePath($eiTypePath);
		if ($eiGrant === null) {
			$eiGrant = new EiGrant();
			$eiGrant->setRocketUserGroup($rocketUserGroup);
			$eiGrant->setEiTypePath($eiTypePath);
		}
		
// 		$privilegeDefinition = $eiEngine->createPrivilegeDefinition($this->getN2nContext());
// 		$securityFilterDefinition = $eiEngine->createSecurityFilterDefinition($this->getN2nContext());
		
		$eiGrantForm = new EiGrantForm($eiGrant, $eiuEngine);
		
		if ($this->dispatch($eiGrantForm, 'save')) {
			if ($eiGrantForm->isNew()) {
				$rocketUserGroup->getEiGrants()->append($eiGrant);
			}

			$this->commit();
			$this->redirectToController(array('grants', $rocketUserGroupId));
			return;
		}
		
		$this->commit();
		
		$this->applyBreadcrumbs();
		$this->applyGrantsBc($rocketUserGroup);
		
		$label = $this->dtc->t('user_type_access_label', ['type' => $eiuEngine->getEiuMask()->getLabel()]);
		$this->rocketState->addBreadcrumb(new Breadcrumb($this->getUrlToPath(), $label));
		
		$this->forward('..\view\grantEdit.html', array('eiGrantForm' => $eiGrantForm, 'label' => $label));
	}	
	
	private function applyBreadcrumbs(RocketUserGroupForm $userGroupForm = null) {
		$httpContext = $this->getHttpContext();
	
		$this->rocketState->addBreadcrumb(new Breadcrumb(
				$httpContext->getControllerContextPath($this->getControllerContext()), 
				$this->dtc->translate('user_groups_title')));
		
		if ($userGroupForm === null) return;
		
		if ($userGroupForm->isNew()) {
			$this->rocketState->addBreadcrumb(new Breadcrumb(
					$httpContext->getControllerContextPath($this->getControllerContext(), array('add')),
					$this->dtc->translate('user_add_group_label')));
		} else {
			$userGroup = $userGroupForm->getRocketUserGroup();
			$this->rocketState->addBreadcrumb(new Breadcrumb(
					$httpContext->getControllerContextPath($this->getControllerContext(), 
							array('edit', $userGroup->getId())),
					$this->dtc->translate('user_edit_group_breadcrumb', array('user_group' => $userGroup->getName()))));
		}
	}
}
