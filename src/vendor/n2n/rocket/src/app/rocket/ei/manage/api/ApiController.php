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
namespace rocket\ei\manage\api;

use n2n\web\http\controller\ControllerAdapter;
use n2n\web\http\controller\ParamPost;
use rocket\ei\manage\ManageState;
use n2n\web\http\BadRequestException;
use n2n\web\http\controller\Param;
use n2n\web\http\controller\ParamBody;
use rocket\si\api\SiGetRequest;
use rocket\si\api\SiGetResponse;
use rocket\si\api\SiValRequest;
use rocket\si\api\SiValResponse;
use rocket\ei\manage\gui\ViewMode;

class ApiController extends ControllerAdapter {
	const API_CONTROL_SECTION = 'execcontrol';
	const API_FIELD_SECTION = 'callfield';
	const API_GET_SECTION = 'get';
	const API_VAL_SECTION = 'val';
	const API_SORT_SECTION = 'sort';
	
	private $eiFrame;
	
	function prepare(ManageState $manageState) {
		$this->eiFrame = $manageState->peakEiFrame();
	}
	
	function index() {
		echo 'very apisch';
	}
	
	static function getApiSections() {
		return [self::API_CONTROL_SECTION, self::API_FIELD_SECTION, self::API_GET_SECTION, self::API_VAL_SECTION, self::API_SORT_SECTION];
	}

	private function parseApiControlCallId(Param $paramQuery) {
		try {
			return ApiControlCallId::parse($paramQuery->parseJson());
		} catch (\InvalidArgumentException $e) {
			throw new BadRequestException(null, null, $e);
		}
	}
	
	private function parseApiFieldCallId(Param $paramQuery) {
		try {
			return ApiFieldCallId::parse($paramQuery->parseJson());
		} catch (\InvalidArgumentException $e) {
			throw new BadRequestException(null, null, $e);
		}
	}
	
	/**
	 * @param Param $paramQuery
	 * @param bool $new
	 * @return number
	 */
	private function parseViewMode(Param $paramQuery, bool $new) {
		$httpData = $paramQuery->parseJsonToHttpData();
		return ViewMode::determine($httpData->reqBool('bulky'), $httpData->reqBool('readOnly'), $new);
	}
	
	/**
	 * @param Param $param
	 * @throws BadRequestException
	 * @return \rocket\si\api\SiGetRequest
	 */
	private function parseGetRequest(Param $param) {
		try {
			return SiGetRequest::createFromData($param->parseJson());
		} catch (\InvalidArgumentException $e) {
			throw new BadRequestException(null, null, $e);
		}
	}
	
	/**
	 * @param Param $param
	 * @throws BadRequestException
	 * @return \rocket\si\api\SiValRequest
	 */
	private function parseValRequest(Param $param) {
		try {
			return SiValRequest::createFromData($param->parseJson());
		} catch (\InvalidArgumentException $e) {
			throw new BadRequestException(null, null, $e);
		}
	}
	
	function postDoGet(ParamBody $param) {
		$siGetRequest = $this->parseGetRequest($param);
		$siGetResponse = new SiGetResponse();
		
		foreach ($siGetRequest->getInstructions() as $key => $instruction) {
			$process = new GetInstructionProcess($instruction, $this->eiFrame);
			$siGetResponse->putResult($key, $process->exec());
		}
		
		$this->sendJson($siGetResponse);
	}

	function postDoVal(ParamBody $param) {
		$siValRequest = $this->parseValRequest($param);
		$siValResponse = new SiValResponse();
		
		foreach ($siValRequest->getInstructions() as $key => $instruction) {
			$process = new ValInstructionProcess($instruction, $this->eiFrame);
			$siValResponse->putResult($key, $process->exec());
		}
		
		$this->sendJson($siValResponse);
	}
	
	function doExecControl(ParamPost $style, ParamPost $apiCallId, ParamPost $entryInputMaps = null) {
		$siApiCallId = $this->parseApiControlCallId($apiCallId);
		
		$callProcess = new ApiControlProcess($this->eiFrame);
		$viewMode = null;
		if (null !== ($pid = $siApiCallId->getPid())) {
			$eiEntry = $callProcess->determineEiEntry($pid);
			$viewMode = $this->parseViewMode($style, false);
			$callProcess->determineEiGuiFrame($viewMode, $eiEntry->getEiType()->getId());
		} else if (null !== ($newEiTypeType = $siApiCallId->getNewEiTypeId())) {
			$callProcess->determineNewEiEntry($newEiTypeType);
			$viewMode = $this->parseViewMode($style, true);
			$callProcess->determineEiGuiFrame($viewMode, $newEiTypeType);
		} else {
			$callProcess->determineEiGuiFrame($viewMode, $siApiCallId->getEiTypeId());
		}
		
		$callProcess->determineGuiControl($siApiCallId->getGuiControlPath());
		
		if ($entryInputMaps !== null
				&& null !== ($siInputError = $callProcess->handleInput($entryInputMaps->parseJson()))) {
			$this->sendJson(SiCallResult::fromInputError($siInputError));
			return;
		}
		
		$this->sendJson(SiCallResult::fromCallResponse($callProcess->callGuiControl()));
	}
	
	function doCallField(ParamPost $style, ParamPost $apiCallId, ParamPost $data) {
		$siApiCallId = $this->parseApiFieldCallId($apiCallId);
		
		$callProcess = new ApiControlProcess($this->eiFrame);
		$viewMode = null;
		if (null !== ($pid = $siApiCallId->getPid())) {
			$callProcess->determineEiEntry($pid);
			$viewMode = $this->parseViewMode($style, false);
		} else {
			$callProcess->determineNewEiEntry($siApiCallId->getEiTypeId());
			$viewMode = $this->parseViewMode($style, true);
		}
		
		$callProcess->determineEiGuiFrame($viewMode, $siApiCallId->getEiTypeId());
		$callProcess->determineGuiField($siApiCallId->getDefPropPath());
		
		$this->sendJson(['data' => $callProcess->callSiField($data->parseJson(), $this->getRequest()->getUploadDefinitions()) ]);
	}
	
	function postDoSort(ParamBody $paramBody) {
		$httpData = $paramBody->parseJsonToHttpData();
		
		$sortProcess = new ApiSortProcess($this->eiFrame);
		
		$sortProcess->determineEiObjects($httpData->reqArray('ids', 'string'));
		
		if (null !== ($afterId = $httpData->optString('afterId'))) {
			$this->sendJson($sortProcess->insertAfter($afterId));
			return;
		}
		
		if (null !== ($beforeId = $httpData->optString('beforeId'))) {
			$this->sendJson($sortProcess->insertBefore($beforeId));
			return;
		}
		
		if (null !== ($parentId = $httpData->optString('parentId'))) {
			$this->sendJson($sortProcess->insertAsChildOf($parentId));
			return;
		}
		
		throw new BadRequestException();
	}
}
