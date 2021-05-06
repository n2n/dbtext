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
namespace rocket\impl\ei\component\prop\file;

use n2n\io\IncompleteFileUploadException;
use n2n\io\UploadedFileExceedsMaxSizeException;
use n2n\io\managed\File;
use n2n\io\managed\impl\FileFactory;
use n2n\io\managed\impl\TmpFileManager;
use n2n\io\orm\ManagedFileEntityProperty;
use n2n\persistence\orm\property\EntityProperty;
use n2n\reflection\property\AccessProxy;
use n2n\util\type\ArgUtils;
use n2n\util\type\CastUtils;
use n2n\util\type\TypeConstraint;
use n2n\util\type\TypeConstraints;
use n2n\validation\lang\ValidationMessages;
use n2n\web\http\Session;
use n2n\web\http\UploadDefinition;
use rocket\ei\EiPropPath;
use rocket\ei\manage\entry\EiFieldValidationResult;
use rocket\ei\util\Eiu;
use rocket\impl\ei\component\prop\adapter\DraftablePropertyEiPropAdapter;
use rocket\impl\ei\component\prop\file\conf\FileConfig;
use rocket\impl\ei\component\prop\file\conf\FileId;
use rocket\impl\ei\component\prop\file\conf\FileVerificator;
use rocket\impl\ei\component\prop\file\conf\ThumbResolver;
use rocket\si\content\SiField;
use rocket\si\content\impl\FileInSiField;
use rocket\si\content\impl\SiFields;
use rocket\si\content\impl\SiFile;
use rocket\si\content\impl\SiFileHandler;
use rocket\si\content\impl\SiUploadResult;
use rocket\impl\ei\component\prop\file\conf\FileModel;
use n2n\io\managed\img\ImageFile;
use n2n\io\managed\img\ImageDimension;
use rocket\ei\manage\idname\IdNameProp;
use rocket\ei\component\prop\IdNameEiProp;
use rocket\ei\util\factory\EifGuiField;

class FileEiProp extends DraftablePropertyEiPropAdapter implements IdNameEiProp {
	
	/**
	 * @var EiPropPath|null
	 */
	private $namingEiPropPath;
	/**
	 * @var ThumbResolver
	 */
	private $thumbResolver;
	/**
	 * @var FileVerificator
	 */
	private $fileVerificator;
	
	
	function __construct() {
		parent::__construct();
		
		$this->thumbResolver = new ThumbResolver();
		$this->fileVerificator = new FileVerificator();
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\impl\ei\component\prop\adapter\DraftablePropertyEiPropAdapter::createEiPropConfigurator()
	 */
	public function prepare() {
		$this->getConfigurator()->addAdaption(
				new FileConfig(new FileModel($this), $this->thumbResolver, $this->fileVerificator));
	}
	
	/**
	 * @return \rocket\impl\ei\component\prop\file\conf\ThumbResolver
	 */
	public function getThumbResolver() {
		return $this->thumbResolver;
	}
	
	/**
	 * @param ThumbResolver $thumbResolver
	 */
	public function setThumbResolver(ThumbResolver $thumbResolver) {
		$this->thumbResolver = $thumbResolver;
	}
	
	/**
	 * @return \rocket\impl\ei\component\prop\file\conf\FileVerificator
	 */
	public function getFileVerificator() {
		return $this->fileVerificator;
	}
	
	/**
	 * @param FileVerificator $fileVerificator
	 */
	public function setFileVerificator(FileVerificator $fileVerificator) {
		$this->fileVerificator = $fileVerificator;
	}
		

// 	public function setMultiUploadEiCommand(MultiUploadEiCommand $multiUploadEiCommand) {
// 		$this->multiUploadEiCommand = $multiUploadEiCommand;
// 	}
	
// 	public function getMultiUploadEiCommand() {
// 		return $this->multiUploadEiCommand;
// 	}
	
	public function setEntityProperty(EntityProperty $entityProperty = null) {
		ArgUtils::assertTrue($entityProperty instanceof ManagedFileEntityProperty);
		$this->entityProperty = $entityProperty;
	}
	
// 	protected function isEntityProperty(?EntityProperty $entityProperty) {
// 		$entityProperty instanceof FileEntityProperty;
// 	}
	

	protected function isObjectPropertyAccessProxyCompatible(?AccessProxy $accessProxy) {
		$accessProxy->getConstraint()->isPassableBy(TypeConstraints::type(File::class));
	}
	
	protected function getObjectPropertyTypeConstraint(TypeConstraint $baseTypeConstraint) {
		return TypeConstraints::type(File::class);
	}
	
	public function setObjectPropertyAccessProxy(AccessProxy $propertyAccessProxy = null) {
		$propertyAccessProxy->setConstraint(TypeConstraint::createSimple('n2n\io\managed\File',
				$propertyAccessProxy->getBaseConstraint()->allowsNull()));
		$this->objectPropertyAccessProxy = $propertyAccessProxy;
	}
	
	public function testEiFieldValue(Eiu $eiu, $eiFieldValue): bool {
		if (!parent::testEiFieldValue($eiu, $eiFieldValue)) {
			return false;
		}
		
		if ($eiFieldValue === null) {
			return true;
		}
		
		\InvalidArgumentException::assertTrue($eiFieldValue instanceof File);
		return $this->fileVerificator->test($eiFieldValue);
	}
	
	public function validateEiFieldValue(Eiu $eiu, $file, EiFieldValidationResult $validationResult) {
		parent::validateEiFieldValue($eiu, $file, $validationResult);
		
		if ($file === null) {
			return;
		}
		
		ArgUtils::assertTrue($file instanceof File);
		
		if (null !== ($message = $this->fileVerificator->validate($file))) {
			$validationResult->addError($message);
		}
	}
	
	function createOutEifGuiField(Eiu $eiu): EifGuiField {
		$siFile = $this->buildSiFileFromEiu($eiu);
		
		return $eiu->factory()->newGuiField(SiFields::fileOut($siFile));
		
// 		if (!$file->isValid()) {
// 			return $html->getEsc('[missing file]');
// 		} 
		
// 		if ($file->getFileSource()->isImage()) {
// 			return $this->createImageUiComponent($view, $eiu, $file);
// 		} 
		
// 		$url = $this->createFileUrl($file, $eiu);
// // 		if ($file->getFileSource()->isHttpaccessible()) {
// 			return new Link($url, $html->getEsc($file->getOriginalName()), array('target' => '_blank'));
// // 		}
		
// // 		return $html->getEsc($file->getOriginalName());
	}
	
// 	private function createFileUrl(File $file, Eiu $eiu) {
// 		if ($file->getFileSource()->isHttpaccessible()) {
// 			return $file->getFileSource()->getUrl();
// 		}
		
// 		return $eiu->frame()->getUrlToCommand($this->thumbEiCommand)->extR(['preview', $eiu->entry()->getPid()]);
// 	}
	
// 	private function createImageUiComponent(HtmlView $view, Eiu $eiu, File $file) {
// 		$html = $view->getHtmlBuilder();
		
// 		$meta = $html->meta();
// 		$html->meta()->addCss('impl/js/thirdparty/magnific-popup/magnific-popup.min.css', 'screen');
// 		$html->meta()->addJs('impl/js/thirdparty/magnific-popup/jquery.magnific-popup.min.js');
// 		$meta->addJs('impl/js/image-preview.js');
		
// 		$uiComponent = new HtmlElement('div', 
// 				array('class' => 'rocket-simple-commands'), 
// 				new Link($this->createFileUrl($file, $eiu), 
// 						$html->getImage($file, ThSt::crop(40, 30, true), array('title' => $file->getOriginalName())), 
// 						array('class' => 'rocket-image-previewable')));
		
// 		if ($this->isThumbCreationEnabled($file) && !$eiu->entry()->isNew()) {
// 			$httpContext = $view->getHttpContext();
// 			$uiComponent->appendContent($html->getLink($eiu->frame()->getUrlToCommand($this->thumbEiCommand)
// 					->extR($eiu->entry()->getPid(), array('refPath' => (string) $eiu->frame()->getEiFrame()->getCurrentUrl($httpContext))),
// 					new HtmlElement('i', array('class' => SiIconType::ICON_CROP), ''),
// 					array('title' => $view->getL10nText('ei_impl_resize_image'),
// 							'class' => 'btn btn-secondary', 'data-jhtml' => 'true')));
// 		}
		
// 		return $uiComponent;
// 	}
	
	public function createInEifGuiField(Eiu $eiu): EifGuiField {
		$siFile = $this->buildSiFileFromEiu($eiu);
		
		$siField = SiFields::fileIn($siFile, $eiu->frame()->getApiFieldUrl(), $eiu->guiField()->createCallId(), 
						new SiFileHanlderImpl($eiu, $this->thumbResolver, $this->fileVerificator, $siFile))
				->setMandatory($this->getEditConfig()->isMandatory())
				->setMaxSize($this->fileVerificator->getMaxSize())
				->setAcceptedExtensions($this->fileVerificator->getAllowedExtensions())
				->setAcceptedMimeTypes($this->fileVerificator->getAllowedMimeTypes())
				->setMessagesCallback(fn () => $eiu->field()->getMessagesAsStrs());
		
		return $eiu->factory()->newGuiField($siField)->setSaver(function () use ($siField, $eiu) {
			$this->saveSiField($siField, $eiu);
		});
		
// 		$allowedExtensions = $this->getAllowedExtensions();
// 		return new FileMag($this->getLabelLstr(), (sizeof($allowedExtensions) ? $allowedExtensions : null), 
// 				$this->isImageRecognized(), null, 
// 				$this->isMandatory($eiu));
	}

	function saveSiField(FileInSiField $siField, Eiu $eiu) {
		
		$siFile = $siField->getValue();
		if ($siFile === null) {
			$eiu->field()->setValue(null);
			return;
		}
		
		$fileId = $siFile->getId();
		CastUtils::assertTrue($fileId instanceof FileId);
		
		$eiu->field()->setValue($file = $this->thumbResolver->determineFile($fileId, $eiu));
		
		$siImageDimensions = $siFile->getImageDimensions();
		if (empty($siImageDimensions) || !$file->getFileSource()->isImage()) {
			return;
		}
		
		$imageFile = new ImageFile($file);
		
		foreach ($siImageDimensions as $siImageDimension) {
			$imageDimension = ImageDimension::createFromString($siImageDimension->getId());
			
			$thumbFileSource = $file->getFileSource()->getAffiliationEngine()->getThumbManager()
					->getByDimension($imageDimension);
			if ($thumbFileSource !== null) {
				$thumbFileSource->delete();
			}
			
			$imageFile->setThumbCut($imageDimension, $siImageDimension->getThumbCut());
		}
	}
	
	/**
	 * @param Eiu $eiu
	 * @return SiFile|null
	 */
	private function buildSiFileFromEiu(Eiu $eiu) {
		$file = $eiu->field()->getValue();
		if ($file === null) {
			return null;
		}
		
		CastUtils::assertTrue($file instanceof File);
		return $this->thumbResolver->createSiFile($file, $eiu, $this->fileVerificator->isImageRecognized());
	}
	
	function buildIdNameProp(Eiu $eiu): ?IdNameProp  {
		return $eiu->factory()->newIdNameProp(function (Eiu $eiu) {
			return $this->buildIdentityString($eiu);
		})->toIdNameProp();
	}
	
	private function buildIdentityString(Eiu $eiu) {
		$file = $eiu->object()->readNativValue($this);
		if ($file === null) return null;
		
		CastUtils::assertTrue($file instanceof File);
		
		if (!$file->isValid()) return (string) $file;
		
		return $file->getOriginalName();
	}
	
	public function copy(Eiu $eiu, $value, Eiu $copyEiu) {
		if ($value === null) return null;

		CastUtils::assertTrue($value instanceof File);
		if (!$value->isValid()) return null;
		
		$tmpFileManager = $copyEiu->lookup(TmpFileManager::class);
		CastUtils::assertTrue($tmpFileManager instanceof TmpFileManager);
		
		return $tmpFileManager->createCopyFromFile($value, $copyEiu->lookup(Session::class, false));
	}
	
	
}

class SiFileHanlderImpl implements SiFileHandler {
	private $eiu;
	private $thumbResolver;
	private $fileVerificator;
	
	function __construct(Eiu $eiu, ThumbResolver $thumbResolver, FileVerificator $fileVerificator,
			?SiFile $currentSiFile) {
		$this->eiu = $eiu;
		$this->thumbResolver = $thumbResolver;
		$this->fileVerificator = $fileVerificator;
	}
	
	function upload(UploadDefinition $uploadDefinition): SiUploadResult {
		/**
		 * @var TmpFileManager $tmpFileManager
		 */
		$tmpFileManager = $this->eiu->lookup(TmpFileManager::class);
		
		$file = null;
		try {
			$file = FileFactory::createFromUploadDefinition($uploadDefinition);
		} catch (UploadedFileExceedsMaxSizeException $e) {
			return SiUploadResult::createError(ValidationMessages
					::uploadMaxSize($e->getMaxSize(), $uploadDefinition->getName(), $uploadDefinition->getSize())
					->t($this->eiu->getN2nLocale()));
		} catch (IncompleteFileUploadException $e) {
			return SiUploadResult::createError(ValidationMessages
					::uploadIncomplete($uploadDefinition->getName())
					->t($this->eiu->getN2nLocale()));
		}

		if (null !== ($message = $this->fileVerificator->validate($file))) {
			return SiUploadResult::createError($message->t($this->eiu->getN2nLocale()));
		}
		
		$tmpFileManager->add($file, $this->eiu->getN2nContext()->getHttpContext()->getSession());
				
		return SiUploadResult::createSuccess($this->thumbResolver->createSiFile($file, $this->eiu,
				$this->fileVerificator->isImageRecognized()));
	}
	
	function getSiFileByRawId(array $rawId): ?SiFile {
		$fileId = FileId::parse($rawId);
		
		$file = $this->thumbResolver->determineFile($fileId, $this->eiu);
		if ($file !== null) {
			return $this->thumbResolver->createSiFile($file, $this->eiu, $this->fileVerificator->isImageRecognized());
		}
		
		return null;
	}
	
// 	function createTmpSiFile(File $file, string $qualifiedName) {
// 		$siFile = new SiFile($file->getOriginalName(), $this->thumbResolver->createTmpUrl($this->eiu, $qualifiedName));
		
// 		if (null !== ($this->thumbResolver->buildThumbFile($file))) {
// 			$siFile->setThumbUrl($this->thumbResolver->createTmpThumbUrl($this->eiu, $qualifiedName,
// 					SiFile::getThumbStrategy()->getImageDimension()));
// 		}
		
// 		return $siFile;
// 	}
}
