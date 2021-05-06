<?php 
namespace rocket\impl\ei\component\prop\translation\command;

use n2n\web\http\controller\ControllerAdapter;
use rocket\ei\util\EiuCtrl;
use n2n\web\http\controller\ParamQuery;
use rocket\ei\manage\DefPropPath;
use n2n\web\http\BadRequestException;
use n2n\impl\web\ui\view\jhtml\JhtmlResponse;
use n2n\util\ex\UnsupportedOperationException;
use n2n\web\dispatch\map\PropertyPath;
use n2n\l10n\N2nLocale;
use n2n\l10n\IllegalN2nLocaleFormatException;

class TranslationCopyController extends ControllerAdapter {
	
	public function doLive(EiuCtrl $eiuCtrl, ParamQuery $defPropPaths, ParamQuery $propertyPath, ParamQuery $bulky,
			ParamQuery $n2nLocale, ParamQuery $pid = null) {
		try {
			$defPropPaths = $this->parseDefPropPaths($defPropPaths);
			$propertyPath = PropertyPath::createFromPropertyExpression((string) $propertyPath);
			$n2nLocale = N2nLocale::create((string) $n2nLocale);
		} catch (\InvalidArgumentException $e) {
			throw new BadRequestException(null, null, $e);
		} catch (IllegalN2nLocaleFormatException $e) {
			throw new BadRequestException(null, null, $e);
		}
		
		$eiuEntry = null;
		if ($pid !== null) {
			$eiuEntry = $eiuCtrl->lookupEntry((string) $pid);
		} else {
			$eiuEntry = $eiuCtrl->frame()->newEntry(false);
			$eiuEntry->getEntityObj()->setN2nLocale($n2nLocale);
		}
		
		foreach ($defPropPaths as $guitFieldPath) {
			if ($eiuEntry->mask()->engine()->containsGuiProp($guitFieldPath)) continue;
			
			throw new BadRequestException('Unknown eiPropPath: ' . $guitFieldPath);
		}
		
		$eiuEntryGui = $eiuEntry->newCustomEntryGui(function () {
			throw new UnsupportedOperationException();
		}, $defPropPaths, $bulky->toBool(), true);
		
		$this->send(JhtmlResponse::view($this->createView('jhtmlTranslation.html',
				array('eiuEntryGui' => $eiuEntryGui, 'propertyPath' => $propertyPath,
						'n2nLocale' => $n2nLocale, 'defPropPaths' => $defPropPaths))));
	}
	
	private function parseDefPropPaths(ParamQuery $param) {
		$eiPropPaths = [];
		foreach ($param->toStringArrayOrReject() as $eiPropPathStr) {
			$eiPropPaths[] = DefPropPath::create((string) $eiPropPathStr);
		}
		if (empty($eiPropPaths)) {
			throw new \InvalidArgumentException('No HuiIdPaths given.');
		}
		return $eiPropPaths;
	}
	
	public function doLiveCopy(EiuCtrl $eiuCtrl, ParamQuery $defPropPaths, ParamQuery $propertyPath, ParamQuery $bulky,
			ParamQuery $toN2nLocale, ParamQuery $fromPid, ParamQuery $toPid = null) {
				
		try {
			$defPropPath = current($this->parseDefPropPaths($defPropPaths));
			$propertyPath = PropertyPath::createFromPropertyExpression((string) $propertyPath);
			$toN2nLocale = N2nLocale::create((string) $toN2nLocale);
		} catch (\InvalidArgumentException $e) {
			throw new BadRequestException(null, null, $e);
		} catch (IllegalN2nLocaleFormatException $e) {
			throw new BadRequestException(null, null, $e);
		}
		
		$fromEiuEntry = $eiuCtrl->lookupEntry((string) $fromPid);
		
		$toEiuEntry = null;
		if ($toPid !== null) {
			$toEiuEntry = $eiuCtrl->lookupEntry((string) $toPid);
		} else {
			$toEiuEntry = $eiuCtrl->frame()->newEntry(false, $fromEiuEntry);
			$toEiuEntry->getEntityObj()->setN2nLocale($toN2nLocale);
		}
		
		if (!$fromEiuEntry->mask()->engine()->containsGuiProp($defPropPath)) {
			throw new BadRequestException('Unknown defPropPath: ' . $defPropPath);
		}
		
		$eiPropPath = $defPropPath->getFirstEiPropPath();
		$fromEiuEntry->copyValuesTo($toEiuEntry, [$eiPropPath]);
		
		$eiuEntryGui = $toEiuEntry->newCustomEntryGui(function () {
			throw new UnsupportedOperationException();
		}, array($defPropPath), $bulky->toBool(), true);
		
		$this->send(JhtmlResponse::view($this->createView('jhtmlTranslation.html', 
				array('eiuEntryGui' => $eiuEntryGui, 'propertyPath' => $propertyPath,
						'n2nLocale' => $toN2nLocale, 'defPropPaths' => [$defPropPath]))));
	}
}
