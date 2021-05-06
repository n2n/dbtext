<?php
namespace rocket\impl\hangar;

use hangar\api\HangarTemplateDef;
use phpbob\representation\PhpClass;
use rocket\impl\ei\component\prop\ci\model\ContentItem;
use phpbob\representation\PhpTypeDef;
use n2n\web\dispatch\mag\MagDispatchable;
use n2n\web\dispatch\mag\MagCollection;
use n2n\impl\web\dispatch\mag\model\MagForm;
use n2n\impl\web\dispatch\mag\model\BoolMag;
use phpbob\representation\PhpFile;
use n2n\impl\web\ui\view\html\HtmlView;
use phpbob\Phpbob;
use n2n\io\IoUtils;
use n2n\io\fs\FsPath;
use n2n\core\TypeLoader;
use n2n\web\hangar\WebTemplateDef;
use n2n\core\config\IoConfig;
use hangar\api\Huo;

class ContentItemTemplateDef implements HangarTemplateDef {
	const PROP_NAME_CREATE_VIEW = 'createView';
	
	public function getName(): string {
		return 'ContentItem';
	}
	
	public function applyTemplate(Huo $huo, PhpClass $phpClass, MagDispatchable $magDispatchable = null) {
		$phpClass->setSuperClassTypeDef(PhpTypeDef::fromTypeName(ContentItem::class));
		WebTemplateDef::applyResponseCacheClearerValue($phpClass, $magDispatchable);
		
		$phpMethod = $phpClass->createPhpMethod('createUiComponent');
		$phpMethod->createPhpParam('view', null, PhpTypeDef::fromTypeName(HtmlView::class));
		
		if ($magDispatchable->getPropertyValue(self::PROP_NAME_CREATE_VIEW)) {
			if ($this->createView($huo, $phpClass)) {
				$phpMethod->setMethodCode("\t\t" . 'return $view->getImport(\'\\' . $phpClass->getPhpNamespace()->getName() . Phpbob::NAMESPACE_SEPERATOR 
						. $this->buildViewName($phpClass->getClassName())  . '\', array(\'' 
						. $this->lowerFirst($phpClass->getClassName()) . '\' => $this));');
			}
		}
	}
	
	private function createView(Huo $huo, PhpClass $phpClass) {
		$viewFilePath = $this->buildViewFilePath($phpClass);
		if (null === $viewFilePath) return false;
		
		$phpFile = new PhpFile();
		$variableName = $this->lowerFirst($phpClass->getClassName());
			
		$phpFile->createUnknownPhpCode(
				"\t" . 'use ' . HtmlView::class . ';'  . PHP_EOL
				. "\t" . 'use ' . $phpClass->getTypeName() . ';'  . PHP_EOL  . PHP_EOL 
				. "\t" . '$view = HtmlView::view($view);' . PHP_EOL
				. "\t" . '$html = HtmlView::html($view);' . PHP_EOL
				. "\t" . '$' . $variableName . ' =  $view->getParam(\'' . $variableName . '\');' . PHP_EOL
				. "\t" . '$view->assert($' . $variableName . ' instanceof ' . $phpClass->getClassName() . ');' . PHP_EOL
				. '?>' . PHP_EOL
				);
		
		$ioConfig = $huo->getAppN2nContext()->lookup(IoConfig::class);
		
		$viewFilePath->mkdirsAndCreateFile($ioConfig->getPrivateDirPermission(), $ioConfig->getPublicDirPermission());
		IoUtils::putContents($viewFilePath, $phpFile->getStringRepresentation());
		
		return true;
	}
	
	private function buildViewFilePath(PhpClass $phpClass) {
		$dirPaths = TypeLoader::getNamespaceDirPaths($phpClass->getPhpNamespace()->getName());
		if (empty($dirPaths)) return false;
		
		return FsPath::create(reset($dirPaths))->ext($this->buildViewName($phpClass->getClassName()) . Phpbob::PHP_FILE_EXTENSION);
	}
	
// 	private function buildViewPathParts(PhpClass $phpClass, bool $addPhpFileExt = false) {
// 		$nameParts = PhpbobUtils::explodeTypeName($phpClass->getTypeName());
// 		return array(reset($nameParts), 
// 				'view', $this->buildViewName($phpClass->getClassName()) . ($addPhpFileExt ? Phpbob::PHP_FILE_EXTENSION : ''));
// 	}
	
	public function createMagDispatchable(): ?MagDispatchable {
		$magCollection = new MagCollection();
		
		WebTemplateDef::addResponseCacheClearerMag($magCollection);
		$magCollection->addMag(self::PROP_NAME_CREATE_VIEW, new BoolMag('Create View?', true));
		
		return new MagForm($magCollection);
	}
	
	private function buildViewName(string $className) {
		return $this->lowerFirst($className) . '.html';
	}
	
	private function lowerFirst(string $str) {
		return mb_strtolower(mb_substr($str, 0, 1)) . mb_substr($str, 1);
	}
}