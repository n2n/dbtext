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
namespace n2n\batch;

use n2n\reflection\magic\MagicMethodInvoker;
use n2n\reflection\ReflectionContext;
use n2n\util\type\CastUtils;

class TriggerInvestigator {
	const ON_TRIGGER_METHOD = '_onTrigger';
	const NEW_HOUR_METHOD = '_onNewHour';
	const NEW_DAY_METHOD = '_onNewDay';
	const NEW_WEEK_METHOD = '_onNewWeek';
	const NEW_MONTH_METHOD = '_onNewMonth';
	const NEW_YEAR_METHOD = '_onNewYear';
	
	const LAST_TRIGGERED_ARG = 'lastTriggered';
	
	private $triggerTracker;
	private $magicMethodInvoker;
	private $batchJob;
	private $class;
	private $lookupId;
	private $now;
	
	public function __construct(TriggerTracker $triggerTracker, MagicMethodInvoker $magicMethodInvoker, $batchJob,
			string $lookupId, \DateTime $now) {
		$this->triggerTracker = $triggerTracker;
		$this->magicMethodInvoker = $magicMethodInvoker;
		$this->batchJob = $batchJob;
		$this->class = new \ReflectionClass($batchJob);
		$this->lookupId = $lookupId;
		$this->now = $now;
	}
	
	public function check(string $methodName, string $dtCheckFormat = null) {
		if (!$this->class->hasMethod($methodName)) return;
		
		$lastTriggered = $this->triggerTracker->getLastTriggered($this->lookupId, $methodName);
		
		if ($lastTriggered === null || $dtCheckFormat === null
				|| $lastTriggered->format($dtCheckFormat) != $this->now->format($dtCheckFormat)) {
			$method = $this->class->getMethod($methodName);
			$method->setAccessible(true);
			$this->magicMethodInvoker->setParamValue(self::LAST_TRIGGERED_ARG, $lastTriggered);
			$this->magicMethodInvoker->setMethod($method);
			$this->magicMethodInvoker->invoke($this->batchJob);
			
			$this->triggerTracker->setLastTriggered($this->lookupId, $methodName, $this->now);
		}
	}
	
	public function checkIntervals() {
		$as = ReflectionContext::getAnnotationSet($this->class);
		
		foreach ($as->getMethodAnnotationsByName(AnnoBatch::class) as $annoBatch) {
			CastUtils::assertTrue($annoBatch instanceof AnnoBatch);
			
			$method = $annoBatch->getAnnotatedMethod();
			$lastTriggered = $this->triggerTracker->getLastTriggered($this->lookupId, $method->getName());
			
			if ($lastTriggered !== null) {
				$nextDt = clone $lastTriggered;
				$nextDt->add($annoBatch->getInterval());
				if ($nextDt > $this->now) continue;
			}
			
			$method->setAccessible(true);
			$this->magicMethodInvoker->setParamValue(self::LAST_TRIGGERED_ARG, $lastTriggered);
			$this->magicMethodInvoker->setMethod($method);
			$this->magicMethodInvoker->invoke($this->batchJob);
			
			$this->triggerTracker->setLastTriggered($this->lookupId, $method->getName(), $this->now);
		}
	}
}