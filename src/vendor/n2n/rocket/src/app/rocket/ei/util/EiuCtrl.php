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
namespace rocket\ei\util;

use n2n\l10n\DynamicTextCollection;
use n2n\persistence\orm\criteria\Criteria;
use n2n\persistence\orm\util\NestedSetStrategy;
use n2n\persistence\orm\util\NestedSetUtils;
use n2n\util\uri\Url;
use n2n\web\http\ForbiddenException;
use n2n\web\http\HttpContext;
use n2n\web\http\Method;
use n2n\web\http\PageNotFoundException;
use n2n\web\http\controller\ParamPost;
use n2n\web\http\controller\impl\ControllingUtils;
use n2n\web\http\payload\impl\Redirect;
use n2n\web\ui\UiComponent;
use rocket\core\model\RocketState;
use rocket\ei\manage\LiveEiObject;
use rocket\ei\manage\ManageState;
use rocket\ei\manage\api\ZoneApiControlCallId;
use rocket\ei\manage\api\ZoneApiControlProcess;
use rocket\ei\manage\entry\UnknownEiObjectException;
use rocket\ei\manage\frame\EiFrame;
use rocket\ei\manage\frame\EiFrameUtil;
use rocket\ei\manage\gui\EiEntryGui;
use rocket\ei\manage\gui\EiGui;
use rocket\ei\manage\gui\EiGuiUtil;
use rocket\ei\manage\gui\ViewMode;
use rocket\ei\manage\preview\model\UnavailablePreviewException;
use rocket\ei\util\entry\EiuEntry;
use rocket\si\SiPayloadFactory;
use rocket\si\content\impl\iframe\IframeData;
use rocket\si\content\impl\iframe\IframeSiGui;
use rocket\si\control\SiNavPoint;
use rocket\si\meta\SiBreadcrumb;
use rocket\ei\manage\gui\control\GuiControl;
use rocket\ei\manage\api\SiCallResult;

class EiuCtrl {
	private $eiu;
	private $eiuFrame;	
	/**
	 * @var EiFrame
	 */
	private $eiFrame;
	/**
	 * @var HttpContext
	 */
	private $httpContext;
	/**
	 * @var RocketState
	 */
	private $rocketState;
	private $cu;
	
	/**
	 * Private so future backwards compatible changes can be made.
	 * @param ControllingUtils $cu
	 */
	private function __construct(ControllingUtils $cu) {
		$this->cu = $cu;
		$manageState = $cu->getN2nContext()->lookup(ManageState::class);
		$this->eiFrame = $manageState->peakEiFrame();
		$this->eiu = new Eiu($manageState->peakEiFrame());
		$this->eiuFrame = $this->eiu->frame();
		$this->httpContext = $manageState->getN2nContext()->getHttpContext();
		$this->rocketState = $cu->getN2nContext()->lookup(RocketState::class);
	}
	
	/**
	 * @return Eiu
	 */
	function eiu() {
		return $this->eiu;
	}
	
	/**
	 * @return \rocket\ei\util\frame\EiuFrame
	 */
	function frame() {
		return $this->eiuFrame;
	}
	
	/**
	 * @param string $livePid
	 * @return \rocket\ei\util\entry\EiuEntry
	 * @throws PageNotFoundException
	 * @throws ForbiddenException
	 */
	function lookupEntry(string $pid, int $ignoreConstraintTypes = 0) {
		return $this->eiuFrame->entry($this->lookupObject($pid, $ignoreConstraintTypes));
	}
	
	/**
	 * @param string $livePid
	 * @return \rocket\ei\util\entry\EiuObject
	 * @throws PageNotFoundException
	 * @throws ForbiddenException
	 */
	function lookupObject(string $pid, int $ignoreConstraintTypes = 0) {
		try {
			return $this->eiuFrame->lookupObject($this->eiuFrame->pidToId($pid), $ignoreConstraintTypes, true);
		} catch (UnknownEiObjectException $e) {
			throw new PageNotFoundException(null, 0, $e);
		} catch (\InvalidArgumentException $e) {
			throw new PageNotFoundException(null, 0, $e);
		} /*catch (InaccessibleEiEntryException $e) {
		throw new ForbiddenException(null, 0, $e);
		}*/
	}
	
	
// 	/**
// 	 * @param EiJhtmlEventInfo $ajahEventInfo
// 	 * @return \rocket\ei\util\EiJhtmlEventInfo|\rocket\ajah\JhtmlEventInfo
// 	 */
// 	private function completeEventInfo(EiJhtmlEventInfo $ajahEventInfo = null) {
// 		if ($ajahEventInfo === null) {
// 			$ajahEventInfo = new EiJhtmlEventInfo();
// 		}
		
// 		$n2nContext = $this->httpContext->getN2nContext();
		
// 		$ajahEventInfo->introduceMessageContainer($n2nContext->lookup(MessageContainer::class));
		
// 		$manageState = $n2nContext->lookup(ManageState::class);
// 		CastUtils::assertTrue($manageState instanceof ManageState);
		
// 		$ajahEventInfo->introduceEiLifecycleMonitor($manageState->getEiLifecycleMonitor());
		
// 		return $ajahEventInfo;
// 	}
	
// 	function redirectToReferer(string $fallbackUrl, EiJhtmlEventInfo $ajahEventInfo = null, JhtmlExec $ajahExec = null) {
// 	    $refererUrl = $this->httpContext->getRequest()->getHeader('Referer');
// 	    if ($refererUrl === null) {
// 	        $refererUrl = $fallbackUrl;
// 	    }
	    
// 	    $response = $this->httpContext->getResponse();
// 	    $acceptRange = $this->httpContext->getRequest()->getAcceptRange();
// 	    if ('application/json' != $acceptRange->bestMatch(['text/html', 'application/json'])) {
// 	        $response->send(new Redirect($refererUrl));
// 	        return;
// 	    }
	    	    
// 	    $response->send(RocketJhtmlResponse::redirectToReferer($refererUrl, 
// 	    		$this->completeEventInfo($ajahEventInfo), $ajahExec));
// 	}
	
// 	function redirectBack(string $fallbackUrl, EiJhtmlEventInfo $ajahEventInfo = null, JhtmlExec $ajahExec = null) {
// 	    $response = $this->httpContext->getResponse();
// 	    $acceptRange = $this->httpContext->getRequest()->getAcceptRange();
// 	    if ('application/json' != $acceptRange->bestMatch(['text/html', 'application/json'])) {
// 	    	$response->send(new Redirect($fallbackUrl));
// 	        return;
// 	    }
	    
// 	    $response->send(RocketJhtmlResponse::redirectBack($fallbackUrl, 
// 	    		$this->completeEventInfo($ajahEventInfo), $ajahExec));
// 	}
	
// 	function redirect(string $url, EiJhtmlEventInfo $ajahEventInfo = null, JhtmlExec $ajahExec = null) {
// 		$response = $this->httpContext->getResponse();
// 		$acceptRange = $this->httpContext->getRequest()->getAcceptRange();
// 		if ('application/json' != $acceptRange->bestMatch(['text/html', 'application/json'])) {
// 			$response->send(new Redirect($url));
// 			return;
// 		}
		
// 		$response->send(RocketJhtmlResponse::redirect($url, 
// 				$this->completeEventInfo($ajahEventInfo), $ajahExec));
// 	}
	
// 	function forwardView(HtmlView $view, EiJhtmlEventInfo $ajahEventInfo = null) {
// 		$response = $this->httpContext->getResponse();
// 		$acceptRange = $this->httpContext->getRequest()->getAcceptRange();
		
// 		if ($ajahEventInfo === null || 'application/json' != $acceptRange->bestMatch(['text/html', 'application/json'])) {
// 			$response->send($view);
// 			return;
// 		}
		
// // 		if ('application/json' == $acceptRange->bestMatch(['text/html', 'application/json'])) {
// // 			$response->send(new AjahResponse($view));
// // 			return;
// // 		}

// 		$response->send(RocketJhtmlResponse::view($view, $this->completeEventInfo($ajahEventInfo)));
// 	}

// 	function buildRedirectUrl($eiEntryArg = null) { 
// 		$eiObject = $eiEntryArg === null ? null : EiuAnalyst::buildEiObjectFromEiArg($eiEntryArg);
// 		$eiFrame = $this->eiuFrame->getEiFrame(); 
		
// 		if ($eiObject !== null && !$eiObject->isNew()) {
// 			$entryNavPoint = $eiObject->toEntryNavPoint();
// 			if ($eiFrame->isDetailUrlAvailable($entryNavPoint)) {
// 				return $eiFrame->getDetailUrl($this->httpContext, $entryNavPoint);
// 			}
// 		}
		
// 		return $eiFrame->getOverviewUrl($this->httpContext);
// 	}
	
// 	function parseRefUrl(ParamQuery $refPath = null) {
// 		if ($refPath === null) return null;
		
// 		try {
// 			$url = Url::create($refPath);
// 			if ($url->isRelative()) return $url;
		
// 			throw new BadRequestException('refPath not relative: ' . $refPath);
// 		} catch (\InvalidArgumentException $e) {
// 			throw new BadRequestException('Invalid refPath: ' . $refPath, null, $e);
// 		}
// 	}
	
// 	function buildRefRedirectUrl(Url $redirectUrl = null, EiObject $eiObject = null) {
// 		if ($redirectUrl !== null) {
// 			return $redirectUrl;	
// 		}
		
// 		return $this->buildRedirectUrl($eiObject);
// 	}
	
// 	function applyCommonBreadcrumbs($eiObjectObj = null, string $currentBreadcrumbLabel = null) {
// 		$eiFrame = $this->eiuFrame->getEiFrame();
// 		$rocketState = $eiFrame->getN2nContext()->lookup(RocketState::class);
// 		CastUtils::assertTrue($rocketState instanceof RocketState);
		
// 		if (!$eiFrame->isOverviewDisabled()) {
// 			$rocketState->addBreadcrumb($eiFrame->createOverviewBreadcrumb($this->httpContext));
// 		}
			
// 		if ($eiObjectObj !== null && !$eiFrame->isDetailDisabled()) {
// 			$eiObject = EiuAnalyst::buildEiObjectFromEiArg($eiObjectObj, 'eiObjectObj', $this->eiuFrame->getContextEiType());
// 			$rocketState->addBreadcrumb($eiFrame->createDetailBreadcrumb($this->httpContext, $eiObject));
// 		}
		
// 		if ($currentBreadcrumbLabel !== null) {
// 			$rocketState->addBreadcrumb(new Breadcrumb($eiFrame->getCurrentUrl($this->httpContext), 
// 					$currentBreadcrumbLabel));
// 		}
// 	}
	
// 	function applyBreadcrumbs(Breadcrumb ...$additionalBreadcrumbs) {
// 		$eiFrame = $this->eiuFrame->getEiFrame();
// 		$rocketState = $eiFrame->getN2nContext()->lookup(RocketState::class);
// 		CastUtils::assertTrue($rocketState instanceof RocketState);
		
// 		foreach ($additionalBreadcrumbs as $additionalBreadcrumb) {
// 			$rocketState->addBreadcrumb($additionalBreadcrumb);
// 		}
// 	}
	
	function lookupPreviewController(string $previewType, $eiObjectArg) {
		try {
			return $this->eiuFrame->lookupPreviewController($previewType, $eiObjectArg);
		} catch (UnavailablePreviewException $e) {
			throw new PageNotFoundException(null, null, $e);
		}
	}
	
	function redirectToOverview(int $status = null) {
		$this->httpContext->getResponse()->send(
				new Redirect($this->eiuFrame->getEiFrame()->getOverviewUrl($this->httpContext), $status));
	}
	
	
	private function forwardHtml() {
		if ('text/html' == $this->httpContext->getRequest()->getAcceptRange()
				->bestMatch(['text/html', 'application/json'])) {
			$this->cu->forward('\rocket\core\view\anglTemplate.html');
			return true;
		}
		
		return false;
	}
	
	function forwardCompactExplorerZone(int $pageSize = 30, string $title = null) {
		if ($this->forwardHtml()) {
			return;
		}
		
		$eiFrame = $this->eiuFrame->getEiFrame();
		$obtainer = $eiFrame->getManageState()->getEiGuiModelCache();
		$eiGuiModel =  $obtainer->obtainEiGuiModel($eiFrame->getContextEiEngine()->getEiMask(), ViewMode::COMPACT_READ, null, true);
		$eiGui = new EiGui($eiGuiModel);
		
		$this->composeEiuGuiForList($eiGui, $pageSize);
		
		$siComp = (new EiGuiUtil($eiGui, $eiFrame))->createCompactExplorerSiGui($pageSize, true, true, []);
		
		$this->httpContext->getResponse()->send(
				SiPayloadFactory::create($siComp,
						$this->rocketState->getBreadcrumbs(),
						$title ?? $this->eiuFrame->contextEngine()->mask()->getPluralLabel()));
	}
	
	
	private function composeEiuGuiForList($eiGui, $limit) {
		
		$eiType = $this->eiuFrame->getEiFrame()->getContextEiEngine()->getEiMask()->getEiType();
		
		$criteria = $this->eiuFrame->getEiFrame()->createCriteria(NestedSetUtils::NODE_ALIAS, false);
		$criteria->select(NestedSetUtils::NODE_ALIAS)->limit($limit);
		
		if (null !== ($nestedSetStrategy = $eiType->getNestedSetStrategy())) {
			$this->treeLookup($eiGui, $criteria, $nestedSetStrategy);
		} else {
			$this->simpleLookup($eiGui, $criteria);
		}
	}
	
	private function simpleLookup(EiGui $eiGui, Criteria $criteria) {
		$eiFrame = $this->eiuFrame->getEiFrame();
		$eiFrameUtil = new EiFrameUtil($eiFrame);
		foreach ($criteria->toQuery()->fetchArray() as $entityObj) {
			$eiObject = new LiveEiObject($eiFrameUtil->createEiEntityObj($entityObj));
			$eiGui->appendEiEntryGui($eiFrame, [$eiFrame->createEiEntry($eiObject)]);
		}
	}
	
	private function treeLookup(EiGui $eiGui, Criteria $criteria, NestedSetStrategy $nestedSetStrategy) {
		$nestedSetUtils = new NestedSetUtils($this->eiuFrame->em(), $this->eiuFrame->getContextEiType()->getEntityModel()->getClass(), $nestedSetStrategy);
		
		$eiFrame = $this->eiuFrame->getEiFrame();
		$eiFrameUtil = new EiFrameUtil($eiFrame);
		foreach ($nestedSetUtils->fetch(null, false, $criteria) as $nestedSetItem) {
			$eiObject = new LiveEiObject($eiFrameUtil->createEiEntityObj($nestedSetItem->getEntityObj()));
			$eiGui->appendEiEntryGui($eiFrame, [$eiFrame->createEiEntry($eiObject)], $nestedSetItem->getLevel());
		}
	}
	
	function forwardBulkyEntryZone($eiEntryArg, bool $readOnly, bool $generalSiControlsIncluded, bool $entrySiControlsIncluded = true,
			array $generalGuiControls = []) {
		if ($this->forwardHtml()) {
			return;
		}

		$eiuEntry = EiuAnalyst::buildEiuEntryFromEiArg($eiEntryArg, $this->eiuFrame, 'eiEntryArg', true);
		$eiuGui = $eiuEntry->newGui(true, $readOnly);
		
		if (null !== ($siResult = $this->handleSiCall($eiuGui->getEiGui()->getEiEntryGui(), $generalGuiControls))) {
			$this->cu->sendJson($siResult);
			return;
		}
		
		$siComp = $eiuGui->createBulkyEntrySiGui($generalSiControlsIncluded, $entrySiControlsIncluded, $generalGuiControls);
		
		$this->httpContext->getResponse()->send(
				SiPayloadFactory::create($siComp,
						$this->rocketState->getBreadcrumbs(),
						$eiuEntry->createIdentityString()));
	}
	
	function forwardNewBulkyEntryZone(bool $editable = true, bool $generalSiControlsIncluded = true, bool $entrySiControlsIncluded = true,
			array $generalGuiControls = []) {
		if ($this->forwardHtml()) {
			return;
		}
		
		$eiFrame = $this->eiuFrame->getEiFrame();
		$eiFrameUtil = new EiFrameUtil($eiFrame);
		
		$eiGui = $eiFrameUtil->createNewEiGui(true, !$editable, null, null, true);
		$eiGuiUtil = new EiGuiUtil($eiGui, $eiFrame);
		
		if (null !== ($siResult = $this->handleSiCall($eiGui->getEiEntryGui(), $generalGuiControls))) {
			$this->cu->sendJson($siResult);
			return;
		}
		
		$siComp = $eiGuiUtil->createBulkyEntrySiGui($generalSiControlsIncluded, $entrySiControlsIncluded, $generalGuiControls);
		
		$this->httpContext->getResponse()->send(
				SiPayloadFactory::create($siComp, 
						$this->rocketState->getBreadcrumbs(),
						$this->eiu->dtc('rocket')->t('common_new_entry_label')));
	}
	
// 	/**
// 	 * @param GuiControl[] $guiControls
// 	 */
// 	private function createSiControls($guiControls) {
// 		return array_map(function ($guiControl) use ($url) {
// 			return $guiControl->toSiControl($this->cu->getRequest()->getUrl(), new ZoneApiControlCallId([$guiControl->getId()]));
// 		}, $guiControls);
// 	}
	
	function forwardIframeZone(UiComponent $uiComponent, bool $useTemplate = true, string $title = null) {
		if ($this->forwardHtml()) {
			return;
		}
		
		$iframeSiGui = null;
		if ($useTemplate) {
			$iframeSiGui = new IframeSiGui(IframeData::createFromUiComponentWithTemplate($uiComponent, $this->eiu->getN2nContext()));
		} else {
			$iframeSiGui = new IframeSiGui(IframeData::createFromUiComponent($uiComponent));
		}
		
		$this->httpContext->getResponse()->send(
				SiPayloadFactory::create($iframeSiGui,
						$this->rocketState->getBreadcrumbs(),
						$title ?? 'Iframe'));
	}
	
	function forwardIframeUrlZone(Url $url, string $title = null) {
		if ($this->forwardHtml()) {
			return;
		}
		
		$iframeSiGui = new IframeSiGui(IframeData::createFromUrl($url));
		
		$this->httpContext->getResponse()->send(
				SiPayloadFactory::create($iframeSiGui,
						$this->rocketState->getBreadcrumbs(),
						$title ?? 'Iframe'));
	}
	
	/**
	 * @param EiEntryGui $eiEntryGui
	 * @param GuiControl[] $guiControls
	 * @return null|\rocket\si\control\SiCallResponse
	 */
	private function handleSiCall(?EiEntryGui $eiEntryGui, array $generalGuiControls) {
		if (!($this->cu->getRequest()->getMethod() === Method::POST && isset($_POST['apiCallId']))) {
			return null;
		}
		
		$process = new ZoneApiControlProcess($this->eiFrame);
		$process->provideEiEntryGui($eiEntryGui);
		$process->determineGuiControl(ZoneApiControlCallId::parse((new ParamPost($_POST['apiCallId']))->parseJson()), $generalGuiControls);
		
		if (isset($_POST['entryInputMaps']) 
				&& null !== ($siInputError = $process->handleInput((new ParamPost($_POST['entryInputMaps']))->parseJson()))) {
			return SiCallResult::fromInputError($siInputError);
		}
		
		return SiCallResult::fromCallResponse($process->callGuiControl());
	}
	
	function forwardUrlIframeZone(Url $url, string $title = null) {
		if ($this->forwardHtml()) {
			return;
		}
		
		$iframeSiGui = new IframeSiGui(IframeData::createFromUrl($url));
		
		$this->httpContext->getResponse()->send(
				SiPayloadFactory::create($iframeSiGui,
						$this->rocketState->getBreadcrumbs(),
						$title ?? 'Iframe'));
	}
	
	/**
	 * @param SiNavPoint $navPoint
	 * @param string $label
	 * @return \rocket\ei\util\EiuCtrl
	 */
	public function pushBreadcrumb(SiNavPoint $navPoint, string $label) {
		$this->rocketState->addBreadcrumb(new SiBreadcrumb($navPoint, $label));
		return $this;
	}
	
	/**
	 * @param Url $url
	 * @param string $label
	 * @return \rocket\ei\util\EiuCtrl
	 */
	public function pushSirefBreadcrumb(Url $url, string $label) {
		$this->rocketState->addBreadcrumb(new SiBreadcrumb(SiNavPoint::siref($url), $label));
		return $this;
	}
	
	/**
	 * @param string $label
	 * @param bool $required
	 * @return \rocket\ei\util\EiuCtrl
	 */
	public function pushOverviewBreadcrumb(string $label = null, bool $required = false) {
		$navPoint = $this->eiuFrame->getOverviewNavPoint($required);
		
		if ($navPoint === null) {
			return $this;
		}
		
		if ($label === null) {
			$label = $this->eiuFrame->getEiFrame()->getContextEiEngine()->getEiMask()->getPluralLabelLstr()
					->t($this->eiu->getN2nLocale());
		}
		
		$this->rocketState->addBreadcrumb(new SiBreadcrumb($navPoint, $label));
		
		return $this;
	}
	
	/**
	 * @param string $label
	 * @param bool $required
	 * @return \rocket\ei\util\EiuCtrl
	 */
	public function pushDetailBreadcrumb($eiObjectArg, string $label = null, bool $required = false) {
		$eiFrame = $this->eiuFrame->getEiFrame();
		$eiObject = EiuAnalyst::buildEiObjectFromEiArg($eiObjectArg, '$eiObjectArg',
				$eiFrame->getContextEiEngine()->getEiMask()->getEiType());
		
		$navPoint = $eiFrame->getDetailNavPoint($eiObject, $required);
		
		if ($navPoint === null) {
			return $this;
		}
		
		if ($label === null) {
			$label = (new EiFrameUtil($eiFrame))->createIdentityString($eiObject);
		}
		
		$this->rocketState->addBreadcrumb(new SiBreadcrumb($navPoint, $label));
		
		return $this;
	}
	
	
	/**
	 * @param string $label
	 * @param bool $required
	 * @return \rocket\ei\util\EiuCtrl
	 */
	public function pushEditBreadcrumb($eiObjectArg, string $label = null, bool $required = false) {
		$eiFrame = $this->eiuFrame->getEiFrame();
		$eiObject = EiuAnalyst::buildEiObjectFromEiArg($eiObjectArg, '$eiObjectArg',
				$eiFrame->getContextEiEngine()->getEiMask()->getEiType());
		
		$navPoint = $eiFrame->getEditNavPoint($eiObject, $required);
		
		if ($navPoint === null) {
			return $this;
		}
		
		if ($label === null) {
			$label = (new DynamicTextCollection('rocket', $this->getN2nContext()->getN2nLocale()))
					->t('common_edit_label');
		}
		
		$this->rocketState->addBreadcrumb(new SiBreadcrumb($navPoint, $label));
		
		return $this;
	}
	/**
	 * @param string $label
	 * @param bool $required
	 * @return \rocket\ei\util\EiuCtrl
	 */
	public function pushAddBreadcrumb(string $label = null, bool $required = false) {
		$navPoint = $this->eiuFrame->getAddNavPoint($required);
		
		if ($navPoint === null) {
			return $this;
		}
		
		if ($label === null) {
			$label = (new DynamicTextCollection('rocket', $this->getN2nContext()->getN2nLocale()))
					->t('common_add_label');
		}
		
		$this->rocketState->addBreadcrumb(new SiBreadcrumb($navPoint, $label));
		
		return $this;
	}
	
	/**
	 * @param string $label
	 * @param bool $includeOverview
	 * @param mixed $detailEiEntryArg
	 * @return EiuCtrl
	 */
	function pushCurrentAsSirefBreadcrumb(string $label, bool $includeOverview = false, $detailEiEntryArg = null) {
		if ($includeOverview) {
			$this->pushOverviewBreadcrumb();
		}
		
		if ($detailEiEntryArg !== null) {
			$this->pushDetailBreadcrumb($detailEiEntryArg);
		}
		
		$this->pushSirefBreadcrumb($this->httpContext->getRequest()->getUrl(), $label);
		
		return $this;
	}
	
	public static function from(ControllingUtils $cu) {
		return new EiuCtrl($cu);
	}
	
	/**
	 * @param object $eiObjectObj
	 * @return \rocket\ei\util\entry\EiuEntry
	 */
	function toEiuEntry($eiObjectObj) {
		return new EiuEntry($eiObjectObj, $this);
	}
}

