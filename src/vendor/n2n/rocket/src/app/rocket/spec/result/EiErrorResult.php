<?php
namespace rocket\spec\result;

use rocket\spec\TypePath;
use rocket\ei\EiPropPath;
use rocket\ei\EiModificatorPath;
use rocket\ei\EiCommandPath;
use n2n\util\type\CastUtils;

class EiErrorResult {
	private $eiPropErrors = [];
	private $eiModificatorErrors = [];
	private $eiCommandErrors = [];
	
	/**
	 * @param EiPropError $eiPropError
	 */
	public function putEiPropError(EiPropError $eiPropError) {
		$this->eiPropErrors[spl_object_hash($eiPropError)] = $eiPropError;
	}
	
// 	/**
// 	 * @param EiProp $eiProp
// 	 * @return EiPropError|null
// 	 */
// 	public function errorOfEiProp(EiProp $eiProp) {
// 		return $this->findEiPropError($eiProp->getWrapper()->getEiPropCollection()->getEiMask()->getEiTypePath(), 
// 				$eiProp->getWrapper()->getEiPropPath());
// 	}
	
// 	/**
// 	 * @param TypePath $typePath
// 	 * @param EiPropPath $eiPropPath
// 	 * @return EiPropError|null
// 	 */
// 	public function findEiPropError(TypePath $typePath, EiPropPath $eiPropPath) {
// 		return ArrayUtils::first(array_filter($this->eiPropErrors, function (EiPropError $eiPropError) use ($typePath, $eiPropPath){
// 			return $eiPropError->getEiPropPath()->equals($eiPropPath)
// 					&& $eiPropError->getEiTypePath()->equals($typePath);
// 		}));
// 	}
	
	/**
	 * @param EiModificatorError $eiModificatorError
	 */
	public function putEiModificatorError(EiModificatorError $eiModificatorError) {
		$this->eiModificatorErrors[spl_object_hash($eiModificatorError)] = $eiModificatorError;
	}
	
// 	/**
// 	 * @param EiProp $eiModificator
// 	 * @return EiModificatorError|null
// 	 */
// 	public function errorOfEiModificator(EiModificator $eiModificator) {
// 		return $this->findEiModificatorError($eiModificator->getWrapper()->getEiModificatorCollection()->getEiMask()->getEiTypePath(), 
// 				$eiModificator->getWrapper()->getEiModificatorPath());
// 	}
	
// 	/**
// 	 * @param TypePath $typePath
// 	 * @param EiModificatorPath $eiModificatorPath
// 	 * @return EiModificatorError|null
// 	 */
// 	public function findEiModificatorError(TypePath $typePath, EiModificatorPath $eiModificatorPath) {
// 		return ArrayUtils::first(array_filter($this->eiModificatorErrors, function (EiModificatorError $eiModificatorError) use ($typePath, $eiModificatorPath){
// 			return $eiModificatorError->getEiModificatorPath()->equals($eiModificatorPath)
// 					&& $eiModificatorError->getEiTypePath()->equals($typePath);
// 		}));
// 	}
	
	/**
	 * @param EiCommandError $eiCommandError
	 */
	public function putEiCommandError(EiCommandError $eiCommandError) {
		$this->eiCommandErrors[spl_object_hash($eiCommandError)] = $eiCommandError;
	}
	
// 	/**
// 	 * @param EiCommand $eiCommand
// 	 * @return EiCommandSetupError|null
// 	 */
// 	public function errorOfEiCommand(EiCommand $eiCommand) {
// 		return $this->findEiCommandError($eiCommand->getWrapper()->getEiCommandCollection()->getEiMask()->getEiTypePath(), 
// 				$eiCommand->getWrapper()->getEiCommandPath());
// 	}
	
// 	/**
// 	 * @param TypePath $typePath
// 	 * @param EiCommandPath $eiCommandPath
// 	 * @return EiCommandSetupError|null
// 	 */
// 	public function findEiCommandError(TypePath $typePath, EiCommandPath $eiCommandPath) {
// 		return ArrayUtils::first($this->getEiCommandErrors($typePath, $eiCommandPath));
// 	}
	
	public function getThrowables(TypePath $typePath = null) {
		$throwables = [];
		array_walk($this->eiPropErrors, function (EiPropError $eiPropError) use ($typePath, $throwables) {
			if ($typePath === null || !$eiPropError->getEiTypePath()->equals($typePath)) return;
			$throwables[] = $eiPropError->getThrowable();
		});
			
		array_walk($this->eiModificatorErrors, function (EiModificatorError $eiModificatorError) use ($typePath, $throwables) {
			if ($typePath === null || !$eiModificatorError->getEiTypePath()->equals($typePath)) return;
			$throwables[] = $eiModificatorError->getThrowable();
		});
			
		array_walk($this->eiCommandErrors, function (EiCommandError $eiCommandError) use ($typePath, $throwables) {
			if ($typePath === null || !$eiCommandError->getEiTypePath()->equals($typePath)) return;
			
			$throwables[] = $eiCommandError->getThrowable();
		});
				
		return $throwables;
	}
	
	public function hasErrors(TypePath $typePath = null) {
		return !empty($this->getThrowables($typePath));
	}
	
	/**
	 * @param TypePath $typePath
	 * @return EiPropError[]
	 */
	public function getEiPropErrors(TypePath $typePath, EiPropPath $eiPropPath = null) {
		return array_filter($this->eiPropErrors, function (EiPropError $eiPropError) use ($typePath, $eiPropPath) {
			return $eiPropError->getEiTypePath()->equals($typePath) 
					&& (null === $eiPropPath || $eiPropError->getEiPropPath()->equals($eiPropPath));
		});
	}
	
	/**
	 * @param TypePath $typePath
	 * @return EiModificatorError[]
	 */
	public function getEiModificatorErrors(TypePath $typePath, EiModificatorPath $eiModificatorPath = null) {
		return array_filter($this->eiModificatorErrors, function (EiModificatorError $eiModificatorError) use ($typePath, $eiModificatorPath) {
			return $eiModificatorError->getEiTypePath()->equals($typePath) 
					&& (null === $eiModificatorPath || $eiModificatorError->getEiModificatorPath()->equals($eiModificatorPath));
		});
	}
	
	/**
	 * @param TypePath $typePath
	 * @return EiCommandError[]
	 */
	public function getEiCommandErrors(TypePath $typePath, EiCommandPath $eiCommandPath = null) {
		return array_filter($this->eiCommandErrors, function (EiCommandError $eiCommandError) use ($typePath, $eiCommandPath) {
			return $eiCommandError->getEiTypePath()->equals($typePath) 
					&& (null === $eiCommandPath || $eiCommandError->getEiCommandPath()->equals($eiCommandPath));
		});
	}
}