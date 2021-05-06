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

use rocket\ei\manage\frame\EiFrame;
use n2n\core\container\N2nContext;
use rocket\ei\manage\ManageException;
use n2n\util\type\ArgUtils;
use rocket\ei\manage\EiObject;
use rocket\ei\manage\entry\EiEntry;
use rocket\ei\manage\EiEntityObj;
use rocket\ei\manage\LiveEiObject;
use rocket\ei\manage\draft\Draft;
use rocket\ei\manage\DraftEiObject;
use rocket\ei\manage\ManageState;
use rocket\ei\manage\gui\EiEntryGui;
use rocket\ei\component\prop\EiProp;
use rocket\ei\EiPropPath;
use rocket\ei\EiType;
use rocket\ei\mask\EiMask;
use rocket\ei\manage\gui\EiGuiFrame;
use rocket\ei\manage\gui\EiEntryGuiAssembler;
use rocket\ei\EiEngine;
use rocket\spec\Spec;
use rocket\ei\EiTypeExtension;
use rocket\ei\component\EiComponent;
use rocket\core\model\Rocket;
use rocket\ei\component\command\EiCommand;
use rocket\ei\EiCommandPath;
use rocket\ei\util\spec\EiuContext;
use rocket\ei\util\spec\EiuEngine;
use rocket\ei\util\spec\EiuMask;
use rocket\ei\util\frame\EiuFrame;
use rocket\ei\util\entry\EiuEntry;
use rocket\ei\util\gui\EiuEntryGui;
use rocket\ei\util\gui\EiuGuiFrame;
use rocket\ei\util\entry\EiuField;
use rocket\ei\util\spec\EiuCommand;
use rocket\ei\util\spec\EiuProp;
use rocket\ei\manage\entry\EiFieldMap;
use rocket\ei\util\entry\EiuFieldMap;
use rocket\ei\util\entry\EiuObject;
use rocket\ei\component\modificator\EiModificator;
use n2n\util\type\TypeUtils;
use rocket\ei\manage\DefPropPath;
use rocket\spec\UnknownTypeException;
use n2n\util\ex\IllegalStateException;
use rocket\ei\util\gui\EiuGuiField;
use rocket\ei\util\gui\EiuGui;
use rocket\ei\manage\gui\EiGui;
use rocket\ei\manage\gui\EiEntryGuiTypeDef;
use rocket\ei\util\gui\EiuEntryGuiTypeDef;
use rocket\ei\manage\gui\EiGuiModel;
use rocket\ei\util\gui\EiuGuiModel;

class EiuAnalyst {
	const EI_FRAME_TYPES = array(EiFrame::class, EiuFrame::class, N2nContext::class);
	const EI_ENTRY_TYPES = array(EiObject::class, EiEntry::class, EiEntityObj::class, Draft::class, 
			EiEntryGui::class, EiuEntry::class, EiuEntryGui::class);
	const EI_GUI_TYPES = array(EiGuiFrame::class, EiuGuiFrame::class, EiEntryGui::class, EiuEntryGui::class);
	const EI_ENTRY_GUI_TYPES = array(EiEntryGui::class, EiuEntryGui::class);
	const EI_TYPES = array(EiFrame::class, N2nContext::class, EiObject::class, EiEntry::class, EiEntityObj::class, 
			Draft::class, EiGuiFrame::class, EiuGuiFrame::class, EiEntryGui::class, EiEntryGui::class, EiProp::class, 
			EiPropPath::class, EiuFrame::class, EiuEntry::class, EiuEntryGui::class, EiuField::class, Eiu::class);
	const EI_FIELD_TYPES = array(EiProp::class, EiPropPath::class, EiuField::class);
	
	protected $n2nContext;
	protected $eiType;
	protected $eiFrame;
	protected $eiObject;
	protected $eiEntry;
	protected $eiGuiModel;
	protected $eiGuiFrame;
	protected $eiGui;
	protected $eiEntryGuiTypeDef;
	protected $eiEntryGui;
	protected $eiEntryGuiAssembler;
	protected $eiPropPath;
	protected $defPropPath;
	protected $eiCommandPath;
	protected $eiEngine;
	protected $spec;
	protected $eiMask;
	
	protected $eiuContext;
	protected $eiuEngine;
	protected $eiuFrame;
	protected $eiuObject;
	protected $eiuEntry;
	protected $eiuFieldMap;
	protected $eiFieldMap;
	protected $eiuGui;
	protected $eiuGuiModel;
	protected $eiuGuiFrame;
	protected $eiuEntryGui;
	protected $eiuEntryGuiTypeDef;
	protected $eiuEntryGuiAssembler;
	protected $eiuGuiField;
	protected $eiuField;
	protected $eiuMask;
	protected $eiuType;
	protected $eiuProp;
	protected $eiuCommand;
	
	public function applyEiArgs(...$eiArgs) {
		$remainingEiArgs = array();
		
		foreach ($eiArgs as $key => $eiArg) {
			if ($eiArg === null) {
				continue;
			}
			
			if ($eiArg instanceof N2nContext) {
				$this->n2nContext = $eiArg;
				continue;
			}
			
			if ($eiArg instanceof EiFrame) {
				$this->assignEiFrame($eiArg);
				continue;
			}
			
			if ($eiArg instanceof EiuFrame) {
				$this->assignEiFrame($eiArg->getEiFrame());
				continue;
			}
			
			if ($eiArg instanceof EiProp) {
				$this->eiPropPath = EiPropPath::from($eiArg);
				$this->assignEiMask($eiArg->getWrapper()->getEiPropCollection()->getEiMask());
				continue;
			}
			
			if ($eiArg instanceof EiPropPath) {
				$this->eiPropPath = $eiArg;
				continue;
			}
			
			if ($eiArg instanceof DefPropPath) {
				$this->defPropPath = $eiArg;
				continue;
			}
			
			if ($eiArg instanceof EiCommand) {
				$this->eiCommandPath = EiCommandPath::from($eiArg);
				$this->assignEiMask($eiArg->getWrapper()->getEiCommandCollection()->getEiMask());
				continue;
			}
			
			if ($eiArg instanceof EiCommandPath) {
				$this->eiCommandPath = $eiArg;
				continue;
			}
			
			if ($eiArg instanceof EiModificator) {
				$this->assignEiMask($eiArg->getWrapper()->getEiModificatorCollection()->getEiMask());
				continue;
			}
			
			if ($eiArg instanceof EiEngine) {
				$this->assignEiEngine($eiArg);
				continue;
			}
			
			if ($eiArg instanceof Spec) {
				$this->spec = $eiArg;
				continue;
			}
			
			if ($eiArg instanceof EiGuiModel) {
				$this->assignEiGuiModel($eiArg);
				continue;
			}
			
			if ($eiArg instanceof EiGuiFrame) {
				$this->assignEiGuiFrame($eiArg);
				continue;
			}
			
			if ($eiArg instanceof EiEntryGuiTypeDef) {
				$this->assignEiEntryGuiTypeDef($eiArg);
				continue;
			}
			
			if ($eiArg instanceof EiEntryGui) {
				$this->assignEiEntryGui($eiArg);
				continue;
			}
			
			if ($eiArg instanceof EiGui) {
				$this->assignEiGui($eiArg);
				continue;
			}
			
			if ($eiArg instanceof EiuGuiField) {
				$this->assignEiuGuiField($eiArg);
				continue;
			}
			
			if ($eiArg instanceof EiEntryGuiAssembler) {
				$this->assignEiEntryGuiAssembler($eiArg);
				continue;
			}
			
			if ($eiArg instanceof EiMask) {
				$this->assignEiMask($eiArg);
				continue;
			}
			
			if ($eiArg instanceof EiComponent) {
				$this->assignEiMask($eiArg->getEiMask());
				continue;
			}
			
			if ($eiArg instanceof EiType) {
				$this->assignEiType($eiArg, true);
				continue;
			}
			
			if ($eiArg instanceof EiTypeExtension) {
				$this->assignEiMask($eiArg->getEiMask());
				continue;
			}
			
			if ($eiArg instanceof EiObject) {
				$this->assignEiObject($eiArg);
				continue;
			}
			
			if ($eiArg instanceof EiEntry) {
				$this->assignEiEntry($eiArg);
				continue;
			}
			
			if ($eiArg instanceof EiFieldMap) {
				$this->assignEiFieldMap($eiArg);
				continue;
			}
			
			if ($eiArg instanceof EiEntityObj) {
				$this->assignEiObject(new LiveEiObject($eiArg));
				continue;
			}
			
			if ($eiArg instanceof Draft) {
				$this->assignEiObject(new DraftEiObject($eiArg));
				continue;
			}
			
			if ($eiArg instanceof EiuField) {
				$this->assignEiuField($eiArg);
				continue;
			}
			
			if ($eiArg instanceof EiuMask) {
				$this->assignEiMask($eiArg->getEiMask());
				continue;
			}
			
			if ($eiArg instanceof EiuEntryGui) {
				$this->assignEiEntryGui($eiArg->getEiEntryGui());
				continue;
			}
			
			if ($eiArg instanceof EiuEntryGuiTypeDef) {
				$this->assignEiEntryGuiTypeDef($eiArg->getEiEntryGuiTypeDef());
				continue;
			}
			
			if ($eiArg instanceof EiuGuiModel) {
				$this->assignEiGuiFrame($eiArg->getEiGuiModel());
				continue;
			}
			
			if ($eiArg instanceof EiuGuiFrame) {
				$this->assignEiGuiFrame($eiArg->getEiGuiFrame());
				continue;
			}
			
			if ($eiArg instanceof EiuGui) {
				$this->assignEiGui($eiArg->getEiGui());
				continue;
			}
			
			if ($eiArg instanceof EiuObject) {
				$this->assignEiObject($eiArg->getEiObject());
				continue;
			}
			
			if ($eiArg instanceof EiuEntry) {
				$this->assignEiEntry($eiArg->getEiEntry());
				continue;
			}
			
			if ($eiArg instanceof EiuFieldMap) {
				$this->assignEiFieldMap($eiArg->getEiFieldMap());
				continue;
			}
			
			if ($eiArg instanceof EiuProp) {
				$this->assignEiProp($eiArg->getEiProp());
				continue;
			}
			
			if ($eiArg instanceof EiuCommand) {
				$this->assignEiCommand($eiArg->getEiCommand());
				continue;
			}
			
			if ($eiArg instanceof EiuEngine) {
				$this->assignEiEngine($eiArg->getEiEngine());
				continue;
			}
			
// 			if ($eiArg instanceof EiuContext) {
// 				$this->assignEiuContext($eiArg);
// 				continue;
// 			}
			
			if ($eiArg instanceof EiuCtrl) {
				$eiArg = $eiArg->eiu();
			}
			
			if ($eiArg instanceof Eiu) {
				$eiuAnalyst = $eiArg->getEiuAnalyst();
				
				if ($eiuAnalyst->n2nContext !== null) {
					$this->n2nContext = $eiuAnalyst->n2nContext;
				}
				if ($eiuAnalyst->eiType !== null) {
					$this->eiType = $eiuAnalyst->eiType;
				}
				if ($eiuAnalyst->eiFrame !== null) {
					$this->eiFrame = $eiuAnalyst->eiFrame;
				}
				if ($eiuAnalyst->eiObject !== null) {
					$this->eiObject = $eiuAnalyst->eiObject;
				}
				if ($eiuAnalyst->eiEntry !== null) {
					$this->eiEntry = $eiuAnalyst->eiEntry;
				}
				if ($eiuAnalyst->eiFieldMap !== null) {
					$this->eiFieldMap = $eiuAnalyst->eiFieldMap;
				}
				if ($eiuAnalyst->eiGuiModel !== null) {
					$this->eiGuiModel = $eiuAnalyst->eiGuiModel;
				}
				if ($eiuAnalyst->eiGuiFrame !== null) {
					$this->eiGuiFrame = $eiuAnalyst->eiGuiFrame;
				}
				if ($eiuAnalyst->eiEntryGui !== null) {
					$this->eiEntryGui = $eiuAnalyst->eiEntryGui;
				}
				if ($eiuAnalyst->eiEntryGuiAssembler !== null) {
					$this->eiEntryGuiAssembler = $eiuAnalyst->eiEntryGuiAssembler;
				}
				if ($eiuAnalyst->eiPropPath !== null) {
					$this->eiPropPath = $eiuAnalyst->eiPropPath;
				}
				if ($eiuAnalyst->defPropPath !== null) {
					$this->defPropPath = $eiuAnalyst->defPropPath;
				}
				if ($eiuAnalyst->eiCommandPath !== null) {
					$this->eiCommandPath = $eiuAnalyst->eiCommandPath;
				}
				if ($eiuAnalyst->eiEngine !== null) {
					$this->eiEngine = $eiuAnalyst->eiEngine;
				}
				if ($eiuAnalyst->spec !== null) {
					$this->spec = $eiuAnalyst->spec;
				}
				if ($eiuAnalyst->eiMask !== null) {
					$this->eiMask = $eiuAnalyst->eiMask;
				}
				
				continue;
			}
			
			$remainingEiArgs[$key + 1] = $eiArg;
		}
		
		if (empty($remainingEiArgs)) return;
		
		$eiType = null;
		$eiObjectTypes = self::EI_TYPES;
		if ($this->eiMask !== null) {
			$eiType = $this->eiMask->getEiType();
		} else if ($this->eiFrame !== null) {
			$eiType = $this->eiFrame->getContextEiEngine()->getEiMask()->getEiType();
			$eiObjectTypes[] = $eiType->getEntityModel()->getClass()->getName();
		}
		
		foreach ($remainingEiArgs as $argNo => $eiArg) {
			if ($eiType !== null && is_object($eiArg)) {
				try {
					$this->eiObject = LiveEiObject::create($eiType, $eiArg);
					continue;
				} catch (\InvalidArgumentException $e) {
					return null;
				}
			}
			
			ArgUtils::valType($eiArg, $eiObjectTypes, true, 'eiArg#' . $argNo);
		}	
	}
	
	/**
	 * @param EiuFrame $eiuFrame
	 */
	private function assignEiuFrame($eiuFrame) {
		if ($this->eiuFrame === $eiuFrame) {
			return;
		}
		
		$this->assignEiFrame($eiuFrame->getEiFrame());
		$this->eiuFrame = $eiuFrame;
	}
	
	/**
	 * @param EiFrame $eiFrame
	 */
	private function assignEiFrame($eiFrame) {
		if ($this->eiFrame === $eiFrame) {
			return;
		}
		
		ArgUtils::assertTrue($this->eiFrame === null, 'EiFrame is not compatible.');
		
		$this->eiFrame = $eiFrame;
		$this->n2nContext = $eiFrame->getN2nContext();
				
		$this->assignEiType($eiFrame->getContextEiEngine()->getEiMask()->getEiType(), true);
	}
	
// 	/**
// 	 * @param EiuEngine $eiuEngine
// 	 */
// 	private function assignEiuEngine($eiuEngine) {
// 		if ($this->eiuEngine === $eiuEngine) {
// 			return;
// 		}
		
// 		$this->assignEiEngine($eiuEngine->getEiEngine());
// 		$this->eiuEngine = $eiuEngine;
// 	}
	
	/**
	 * @param EiEngine $eiEngine
	 */
	private function assignEiEngine($eiEngine) {
		if ($this->eiEngine === $eiEngine) {
			return;
		}
		
		$this->eiuEngine = null;
		$this->eiEngine = $eiEngine;
		
		$this->assignEiMask($eiEngine->getEiMask());
	}
	
// 	/**
// 	 * @param EiuProp $eiuProp
// 	 */
// 	private function assignEiuProp($eiuProp) {
// 		if ($this->eiuProp === $eiuProp) {
// 			return;
// 		}
		
// 		$this->assignEiuEngine($eiuProp->getEiuEngine());
// 		$this->eiPropPath = $eiuProp->getEiPropPath();
// 		$this->eiuProp = $eiuProp;
// 	}
		
// 	/**
// 	 * @param EiuCommand $eiuCommand
// 	 */
// 	private function assignEiuCommand($eiuCommand) {
// 		if ($this->eiuCommand === $eiuCommand) {
// 			return;
// 		}
		
// 		$this->assignEiuEngine($eiuCommand->getEiuEngine());
// 		$this->eiCommandPath = $eiuProp->getEiCommandPath();
// 		$this->eiuCommand = $eiuCommand;
// 	}
	
	
// 	/**
// 	 * @param EiuMask $eiuMask
// 	 */
// 	private function assignEiuMask($eiuMask) {
// 		if ($this->eiuMask === $eiuMask) {
// 			return;
// 		}
		
// 		$this->assignEiMask($eiuMask->getEiMask());
// 		$this->eiuMask = $eiuMask;
// 	}
	
	/**
	 * @param EiMask $eiMask
	 */
	private function assignEiMask($eiMask) {
		if ($this->eiMask === $eiMask) {
			return;
		}
		
		$this->eiuMask = null;
		$this->eiMask = $eiMask;
		
		$this->assignEiType($eiMask->getEiType(), false);
		
		if ($eiMask->hasEiEngine()) {
			$this->assignEiEngine($eiMask->getEiEngine());
		}
	}
	
	/**
	 * @param EiType $eiType
	 */
	private function assignEiType($eiType, bool $contextOnly) {
		if ($this->eiType === $eiType) {
			return;
		}
		
		if ($this->eiType === null || $eiType->isA($this->eiType)) {
			$this->eiType = $eiType;
			return;
		}
		
		if ($this->eiType->isA($eiType)) {
			return;
		}
		
		throw new \InvalidArgumentException('Incompatible EiTypes ' . $this->eiType->getId() . ' / ' 
				. $eiType->getId());
	}
	
// 	/**
// 	 * @param EiuGuiFrame $eiuGuiFrameLayout
// 	 */
// 	private function assignEiuGui($eiuGuiFrameLayout) {
// 		if ($this->eiuGuiFrameLayout === $eiuGuiFrameLayout) {
// 			return;
// 		}
		
// 		$this->assignEiGui($EiGui);
// 		$this->eiuGuiFrameLayout = $eiuGuiFrameLayout;
// 	}
	
	/**
	 * @param EiGui $EiGui
	 */
	private function assignEiGui($eiGui) {
		if ($this->eiGui === $eiGui) {
			return;
		}
		
		$this->assignEiGuiModel($eiGui->getEiGuiModel());
		
		$this->eiuGui = null;
		$this->eiGui = $eiGui;
		
// 		$eiEntryGuis = $eiGui->getEiEntryGuis();
// 		if (count($eiEntryGuis) == 1) {
// 			$this->assignEiEntryGui(current($eiEntryGuis));
// 		}
	}
	
// 	/**
// 	 * @param EiuGuiFrame $eiuGuiFrame
// 	 */
// 	private function assignEiuGuiFrame($eiuGuiFrame) {
// 		if ($this->eiuGuiFrame === $eiuGuiFrame) {
// 			return;
// 		}
		
// 		$this->assignEiGuiFrame($eiuGuiFrame->getEiGuiFrame());
// 		$this->eiuGuiFrame = $eiuGuiFrame;
// 	}
	
	/**
	 * @param EiGuiModel $eiGuiModel
	 */
	private function assignEiGuiModel($eiGuiModel) {
		if ($this->eiGuiModel === $eiGuiModel) {
			return;
		}
		
		$this->eiGuiModel = null;
		$this->eiGuiModel = $eiGuiModel;
		
		
		// 		$eiEntryGuis = $eiGuiFrame->getEiEntryGuis();
		// 		if (count($eiEntryGuis) == 1) {
		// 			$this->assignEiEntryGui(current($eiEntryGuis));
		// 		}
	}
	
	/**
	 * @param EiGuiFrame $eiGuiFrame
	 */
	private function assignEiGuiFrame($eiGuiFrame) {
		if ($this->eiGuiFrame === $eiGuiFrame) {
			return;
		}
		
		$this->eiuGuiFrame = null;
		$this->eiGuiFrame = $eiGuiFrame;
		
		$this->assignEiMask($eiGuiFrame->getGuiDefinition()->getEiMask());
		
// 		$eiEntryGuis = $eiGuiFrame->getEiEntryGuis();
// 		if (count($eiEntryGuis) == 1) {
// 			$this->assignEiEntryGui(current($eiEntryGuis));
// 		}
	}
	
// 	/**
// 	 * @param EiuEntryGui $eiuEntryGui
// 	 */
// 	private function assignEiuEntryGui($eiuEntryGui) {
// 		if ($this->eiuEntryGui === $eiuEntryGui) {
// 			return;
// 		}
		
// 		$this->assignEiuGuiFrame($eiuEntryGui->guiFrame());
// 		$this->assignEiEntryGui($eiuEntryGui->getEiEntryGui());
// 		$this->eiuEntryGui = $eiuEntryGui;
// 	}

	/**
	 * @param EiEntryGuiTypeDef $eiEntryGuiTypeDef
	 */
	private function assignEiEntryGuiTypeDef($eiEntryGuiTypeDef) {
		if ($this->eiEntryGuiTypeDef === $eiEntryGuiTypeDef) {
			return;
		}
		
		IllegalStateException::assertTrue($this->eiEntryGuiTypeDef === null);
		
		$this->eiuEntryGuiTypeDef = null;
		$this->eiEntryGuiTypeDef = $eiEntryGuiTypeDef;
		
		$this->assignEiEntry($eiEntryGuiTypeDef->getEiEntry());
		$this->assignEiEntryGui($eiEntryGuiTypeDef->getEiEntryGui());
	}
	
	/**
	 * @param EiEntryGui $eiEntryGui
	 */
	private function assignEiEntryGui($eiEntryGui) {
		if ($this->eiEntryGui === $eiEntryGui) {
			return;
		}
		
		$this->eiuEntryGui = null;
		$this->eiEntryGui = $eiEntryGui;
		
		if ($eiEntryGui->isTypeDefSelected()) {
			$this->assignEiEntryGuiTypeDef($eiEntryGui->getSelectedTypeDef());
		}
		
		$this->assignEiGui($eiEntryGui->getEiGui());
	}
	
// 	/**
// 	 * @param EiuEntryGuiAssembler $eiuEntryGuiAssembler
// 	 */
// 	private function assignEiuGuiFrameAssembler($eiuEntryGuiAssembler) {
// 		if ($this->eiuEntryGuiAssembler === $eiuEntryGuiAssembler) {
// 			return;
// 		}
		
// 		$this->assignEiEntryGuiAssembler($eiuEntryGuiAssembler->getEiEntryGuiAssembler());
// // 		$this->assignEiuEntryGui($eiuEntryGuiAssembler->getEiuEntryGui());
// 		$this->eiuEntryGuiAssembler = $eiuEntryGuiAssembler;
// 	}
	
	/**
	 * @param EiEntryGuiAssembler $eiEntryGuiAssembler
	 */
	private function assignEiEntryGuiAssembler($eiEntryGuiAssembler) {
		if ($this->eiEntryGuiAssembler === $eiEntryGuiAssembler) {
			return;
		}
		
		$this->eiuEntryGuiAssembler = null;
		$this->eiEntryGuiAssembler = $eiEntryGuiAssembler;
		
		$this->assignEiEntryGui($eiEntryGuiAssembler->getEiEntryGui());
	}
	
// 	/**
// 	 * @param EiuField $eiuField
// 	 */
// 	private function assignEiuField($eiuField) {
// 		if ($this->eiuField === $eiuField) {
// 			return;
// 		}
		
// 		$this->assignEiuEntry($eiuField->getEiuEntry());
		
// 		$this->eiuField = $eiuField;
// 		$this->eiPropPath = $eiuField->getEiPropPath();
// 	}
	
// 	/**
// 	 * @param EiuGuiField $eiuGuiFrameField
// 	 */
// 	private function assignEiuGuiField($eiuGuiFrameField) {
// 		if ($this->eiuGuiFrameField === $eiuGuiFrameField) {
// 			return;
// 		}
		
// 		$this->assignEiuEntryGui($eiuGuiFrameField->getEiuEntryGui());
		
// 		$this->eiuField = $eiuField;
// 		$this->eiPropPath = $eiuField->getEiPropPath();
// 	}
	
// 	/**
// 	 * @param EiuEntry $eiuEntry
// 	 */
// 	private function assignEiuObject($eiuObject) {
// 		if ($this->eiuObject === $eiuObject) {
// 			return;
// 		}
		
// 		$this->assignEiObject($eiuObject->getEiObject());
		
// 		$this->eiuObject = $eiuObject;
// 	}
	
// 	/**
// 	 * @param EiuEntry $eiuEntry
// 	 */
// 	private function assignEiuEntry($eiuEntry) {
// 		if ($this->eiuEntry === $eiuEntry) {
// 			return;
// 		}
		
// 		if (null !== ($eiEntry = $eiuEntry->getEiEntry(false))) {
// 			$this->assignEiEntry($eiEntry);
// 		} else {
// 			$this->assignEiObject($eiuEntry->object()->getEiObject());
// 		}
		
// 		if (null !== ($eiuFrame = $eiuEntry->getEiuFrame(false))) {
// 			$this->assignEiuFrame($eiuFrame);
// 		}
		
// 		$this->eiuEntry = $eiuEntry;
// 	}
	
// 	private function assignEiuFieldMap($eiuFieldMap) {
// 		if ($this->eiuFieldMap === $eiuFieldMap) {
// 			return;
// 		}
		
// 		$this->assignEiFieldMap($eiuFieldMap->getEiFieldMap());
		
// 		$this->eiuFieldMap = $eiuFieldMap;
// 	}
	
	/**
	 * @param EiEntry $eiObject
	 */
	private function assignEiEntry($eiEntry) {
		if ($this->eiEntry === $eiEntry) {
			return;
		}
		
		$this->eiuEntry = null;
		$this->eiEntry = $eiEntry;
		
		$this->assignEiObject($eiEntry->getEiObject());
		$this->assignEiFieldMap($eiEntry->getEiFieldMap());
		$this->assignEiMask($eiEntry->getEiMask());
	}
	
	private function assignEiFieldMap($eiFieldMap) {
		if ($this->eiFieldMap === $eiFieldMap) {
			return;
		}
		
		$this->eiuFieldMap = null;
		$this->eiFieldMap = $eiFieldMap;
	}
	
	/**
	 * @param EiObject $eiObject
	 */
	private function assignEiObject($eiObject) {
		if ($this->eiObject === $eiObject) {
			return;
		}
		
		$this->eiuObject = null;
		$this->eiuEntry = null;
		$this->eiObject = $eiObject;
		
		$this->assignEiType($eiObject->getEiEntityObj()->getEiType(), false);
	}
	
// 	public function getEiFrame(bool $required) {
// 		if (!$required || $this->eiFrame !== null) {
// 			return $this->eiEntryGui;
// 		}
	
// 		throw new EiuPerimeterException(
// 				'Could not determine EiuFrame because non of the following types were provided as eiArgs: '
// 						. implode(', ', self::EI_FRAME_TYPES));
// 	}
	
	/**
	 *
	 * @param bool $required
	 * @throws EiuPerimeterException
	 * @return \rocket\ei\manage\frame\EiFrame|null
	 */
	public function getEiFrame(bool $required) {
		if (!$required || $this->eiFrame !== null) {
			return $this->eiFrame;
		}
		
		throw new EiuPerimeterException(
				'Could not determine EiFrame because non of the following types were provided as eiArgs: '
				. implode(', ', self::EI_FRAME_TYPES));
	}
	
	/**
	 * @return NULL|\rocket\ei\manage\entry\EiEntry
	 */
	public function getEiEntry() {
		return $this->eiEntry;
	}
	
	/**
	 * 
	 * @param bool $required
	 * @throws EiuPerimeterException
	 * @return \rocket\ei\manage\EiObject|NULL
	 */
	public function getEiObject(bool $required) {
		if (!$required || $this->eiObject !== null) {
			return $this->eiObject;
		}
	
		throw new EiuPerimeterException(
				'Could not determine EiObject because non of the following types were provided as eiArgs: '
						. implode(', ', self::EI_ENTRY_TYPES));
	}
	
	/**
	 * @param bool $required
	 * @throws EiuPerimeterException
	 * @return \rocket\ei\manage\gui\EiGui
	 */
	public function getEiGui(bool $required) {
		if (!$required || $this->eiGui !== null) {
			return $this->eiGui;
		}
	
		throw new EiuPerimeterException(
				'Could not determine EiGui because non of the following types were provided as eiArgs: '
						. implode(', ', self::EI_GUI_TYPES));
	}
	
	/**
	 * @param bool $required
	 * @throws EiuPerimeterException
	 * @return \rocket\ei\manage\gui\EiEntryGui
	 */
	public function getEiEntryGui(bool $required) {
		if (!$required || $this->eiEntryGui !== null) {
			return $this->eiEntryGui;
		}
		
		throw new EiuPerimeterException(
				'Could not determine EiEntryGui because non of the following types were provided as eiArgs: '
				. implode(', ', self::EI_ENTRY_GUI_TYPES));
	}
	
	/**
	 * @param bool $required
	 * @throws EiuPerimeterException
	 * @return \rocket\ei\manage\gui\EiEntryGuiAssembler
	 */
	public function getEiEntryGuiAssembler(bool $required) {
		if (!$required || $this->eiEntryGuiAssembler !== null) {
			return $this->eiEntryGuiAssembler;
		}
		
		throw new EiuPerimeterException('Could not determine EiEntryGuiAssembler.');
	}
	
	public function getEiPropPath(bool $required) {
		if (!$required || $this->eiPropPath !== null) {
			return $this->eiPropPath;
		}
		
		throw new EiuPerimeterException(
				'Could not determine EiPropPath because non of the following types were provided as eiArgs: '
				. implode(', ', self::EI_FIELD_TYPES));
	}
	
	public function getEiCommandPath(bool $required) {
		if (!$required || $this->eiCommandPath !== null) {
			return $this->eiCommandPath;
		}
		
		throw new EiuPerimeterException(
				'Could not determine EiCommandPath because non of the following types were provided as eiArgs: '
				. implode(', ', self::EI_FIELD_TYPES));
	}
	
	/**
	 * @param bool $required
	 * @throws EiuPerimeterException
	 * @return \rocket\ei\manage\DefPropPath
	 */
	public function getDefPropPath(bool $required) {
		if (!$required || $this->defPropPath !== null) {
			return $this->defPropPath;
		}
		
		throw new EiuPerimeterException(
				'Could not determine DefPropPath because non of the following types were provided as eiArgs: '
				. implode(', ', self::EI_FIELD_TYPES));
	}
	
	/**
	 * @param bool $required
	 * @throws EiuPerimeterException
	 * @return EiEngine
	 */
	public function getEiEngine(bool $required) {
		if (!$required || $this->eiEngine !== null) {
			return $this->eiEngine;
		}
		
		throw new EiuPerimeterException('Could not determine EiEngine.');
	}
	
	/**
	 * @param bool $required
	 * @throws EiuPerimeterException
	 * @return Spec
	 */
	public function getSpec(bool $required) {
		if ($this->spec !== null) {
			return $this->spec;
		}
		
		if ($this->n2nContext !== null) {
			return $this->n2nContext->lookup(Rocket::class)->getSpec();
		}
		
		throw new EiuPerimeterException('Could not determine Spec.');
	}
	
	/**
	 * @param bool $required
	 * @throws EiuPerimeterException
	 * @return N2nContext
	 */
	public function getN2nContext(bool $required) {
		if (!$required || $this->n2nContext !== null) {
			return $this->n2nContext;
		}
		
		throw new EiuPerimeterException('Could not determine N2nContext.');
	}
	
	/**
	 * @return ManageState
	 */
	function getManageState() {
		return $this->getN2nContext(true)->lookup(ManageState::class);
	}
	
	public function getEiuContext(bool $required) {
		if ($this->eiuContext !== null) {
			return $this->eiuContext;
		}
		
		$spec = null;
		try {
			$spec = $this->getSpec($required);
		} catch (EiuPerimeterException $e) {
			throw new EiuPerimeterException('Could not determine EiuContext.', 0, $e);
		}
		
		if ($spec === null) return null;
		
		return $this->eiuContext = new EiuContext($spec, $this);
	}
	
	/**
	 * @param bool $required
	 * @throws EiuPerimeterException
	 * @return EiuEngine
	 */
	public function getEiuEngine(bool $required) {
		if ($this->eiuEngine !== null) {
			return $this->eiuEngine;
		}
		
		if ($this->eiEngine === null && $this->eiMask !== null && ($required || $this->eiMask->hasEiEngine())) {
			$this->eiEngine = $this->eiMask->getEiEngine();
		}
		
		if ($this->eiEngine !== null) {
			return $this->eiuEngine = new EiuEngine($this->eiEngine, $this->eiuMask, $this);
		}
		
		if (!$required) return null;
		
		throw new EiuPerimeterException('Can not create EiuEngine.');
	}
	
	/**
	 * @param bool $required
	 * @throws EiuPerimeterException
	 * @return \rocket\ei\mask\EiMask|NULL
	 */
	function getEiMask(bool $required) {
		if ($this->eiMask !== null) {
			return $this->eiMask;
		}
		
		if ($this->eiFrame !== null) {
			return $this->eiFrame->getContextEiEngine()->getEiMask();
		}
		
		if (!$required) {
			return null;
		}
		
		throw new EiuPerimeterException('EiMask not avaialble');
	}
	
	/**
	 * @param bool $required
	 * @throws EiuPerimeterException
	 * @return EiuMask
	 */
	public function getEiuMask(bool $required) {
		if ($this->eiuMask !== null) {
			return $this->eiuMask;
		}
		
		if (null !== ($eiMask = $this->getEiMask(false))) {
			return $this->eiuMask = new EiuMask($eiMask, $this->eiuEngine, $this);
		}
		
		if (!$required) return null;
		
		throw new EiuPerimeterException('EiuMask not avaialble');
	}
	/**
	 * @throws EiuPerimeterException
	 * @return \rocket\ei\util\frame\EiuFrame
	 */
	public function getEiuFrame(bool $required) {
		if ($this->eiuFrame !== null) {
			return $this->eiuFrame;
		}
		
		if ($this->eiFrame !== null) {
			return $this->eiuFrame = new EiuFrame($this->eiFrame, $this);
		}
		
// 		if ($this->n2nContext !== null) {
// 			try {
// 				return $this->eiuFrame = new EiuFrame($this->n2nContext->lookup(ManageState::class)->peakEiFrame(), $this);
// 			} catch (ManageException $e) {
// 				if (!$required) return null;
				
// 				throw new EiuPerimeterException('Can not create EiuFrame in invalid context.', 0, $e);
// 			}
// 		}
		
		if (!$required) return null;
		
		throw new EiuPerimeterException('No EiuFrame avialble.');
	}
	
	public function getEiuObject(bool $required) {
		if ($this->eiuObject !== null) {
			return $this->eiuObject;
		}
		
		if ($this->eiObject !== null) {
			return $this->eiuObject = new EiuObject($this->eiObject, $this); 
		}	
		
		if (!$required) return null;
		
		throw new EiuPerimeterException('No EiuObject avialble.');
	}
	
	public function getEiuEntry(bool $required) {
		if ($this->eiuEntry !== null) {
			return $this->eiuEntry;
		}
		
		$eiuFrame = $this->getEiuFrame(false);
		
		if ($eiuFrame !== null) {
			if ($this->eiEntry !== null) {
				return $this->eiuEntry = new EiuEntry($this->eiEntry, $this->getEiuObject(true), null, $this);
			}
			if ($this->eiObject !== null) {
				return $this->eiuEntry = $eiuFrame->entry($this->eiObject);
			}
		} else {
			if ($this->eiEntry !== null) {
				return $this->eiuEntry = new EiuEntry($this->eiEntry, $this->getEiuObject(true), null, $this);
			}
// 			if ($this->eiObject !== null) {
// 				return $this->eiuEntry = new EiuEntry(null, $this->getEiuObject(true), null, $this);
// 			}
		}
		
		if (!$required) return null;
		
		throw new EiuPerimeterException('No EiuEntry available.');
	}
	
	public function getEiuFieldMap(bool $required) {
		if ($this->eiuFieldMap !== null) {
			return $this->eiuFieldMap;
		}
		
		if ($this->eiFieldMap !== null) {
			return $this->eiuFieldMap = new EiuFieldMap($this->eiFieldMap, $this);
		}
		
		if (!$required) return null;
		
		throw new EiuPerimeterException('New EiuFieldMap available.');
	}
	
	public function getEiuProp(bool $required) {
		if ($this->eiuProp !== null) {
			return $this->eiuProp;
		}
		
		return $this->eiuProp = new EiuProp($this->getEiPropPath(true), $this->getEiuMask(true), $this);
	}
	
	/**
	 * @param bool $required
	 * @throws EiuPerimeterException
	 * @return \rocket\ei\util\gui\EiuEntryGui
	 */
	public function getEiuEntryGui(bool $required) {
		if ($this->eiuEntryGui !== null) {
			return $this->eiuEntryGui;
		}
		
		if ($this->eiEntryGui !== null) {
			return $this->eiuEntryGui = new EiuEntryGui($this->eiEntryGui, $this->getEiuGui(true), $this);
		}
		
		if (!$required) return null;
		
		throw new EiuPerimeterException(
				'Can not create EiuEntryGui because non of the following types were provided as eiArgs: '
						. implode(', ', self::EI_ENTRY_GUI_TYPES));
	}
	
	/**
	 * @param bool $required
	 * @throws EiuPerimeterException
	 * @return \rocket\ei\util\gui\EiuEntryGuiTypeDef
	 */
	public function getEiuEntryGuiTypeDef(bool $required) {
		if ($this->eiuEntryGuiTypeDef !== null) {
			return $this->eiuEntryGuiTypeDef;
		}
		
		if ($this->eiEntryGuiTypeDef !== null) {
			return $this->eiuEntryGuiTypeDef = new EiuEntryGuiTypeDef($this->eiEntryGuiTypeDef, $this->getEiuEntryGui(false), $this);
		}
		
		if (!$required) return null;
		
		throw new EiuPerimeterException(
				'Can not create EiuEntryGui because non of the following types were provided as eiArgs: '
				. implode(', ', self::EI_ENTRY_GUI_TYPES));
	}
	
	/**
	 * @param bool $required
	 * @throws EiuPerimeterException
	 * @return \rocket\ei\util\gui\EiuGuiFrame
	 */
	public function getEiuGui(bool $required) {
		if ($this->eiuGui !== null) {
			return $this->eiuGui;
		}
		
		if ($this->eiGui !== null) {
			return $this->eiuGui = new EiuGui($this->eiGui, $this->eiuGuiModel, $this);
		}
		
		if (!$required) return null;
		
		throw new EiuPerimeterException('Can not create EiuGui because non of the following types were provided as eiArgs: '
				. implode(', ', self::EI_GUI_TYPES));
	}
	
	/**
	 * @param bool $required
	 * @throws EiuPerimeterException
	 * @return \rocket\ei\util\gui\EiuGuiModel
	 */
	public function getEiuGuiModel(bool $required) {
		if ($this->eiuGuiModel !== null) {
			return $this->eiuGuiModel;
		}
		
		if ($this->eiGuiModel !== null) {
			return $this->eiGuiModel = new EiuGuiModel($this->eiGuiModel, $this);
		}
		
		if (!$required) return null;
		
		throw new EiuPerimeterException(
				'Can not create EiuGuiModel because non of the following types were provided as eiArgs: '
				. implode(', ', self::EI_GUI_TYPES));
	}
	
	/**
	 * @param bool $required
	 * @throws EiuPerimeterException
	 * @return \rocket\ei\util\gui\EiuGuiFrame
	 */
	public function getEiuGuiFrame(bool $required) {
		if ($this->eiuGuiFrame !== null) {
			return $this->eiuGuiFrame;
		}
	
		if ($this->eiGuiFrame !== null) {
			return $this->eiuGuiFrame = new EiuGuiFrame($this->eiGuiFrame, $this->eiuGui, $this);
		}
	
		if (!$required) return null;
	
		throw new EiuPerimeterException(
				'Can not create EiuGuiFrame because non of the following types were provided as eiArgs: '
						. implode(', ', self::EI_GUI_TYPES));
	}
	
	public function getEiuField(bool $required) {
		if ($this->eiuField !== null) {
			return $this->eiuField;
		}
	
		$eiuEntry = $this->getEiuEntry(false);
		if ($eiuEntry !== null) {
			if ($this->eiPropPath !== null) {
				return $this->eiuField = $eiuEntry->field($this->eiPropPath);
			}
		} else {
			if ($this->eiPropPath !== null) {
				return $this->eiuField = new EiuField($this->eiPropPath);
			}
		}
	
		if (!$required) return null;
	
		throw new EiuPerimeterException(
				'Can not create EiuField because non of the following types were provided as eiArgs: '
						. implode(', ', self::EI_FIELD_TYPES));
	}
	
	public function getEiuGuiField(bool $required) {
		if ($this->eiuGuiField !== null) {
			return $this->eiuGuiField;
		}
		
		$eiuEntryGuiTypeDef = $this->getEiuEntryGuiTypeDef(false);
		if ($eiuEntryGuiTypeDef !== null) {
			if ($this->defPropPath !== null) {
				return $this->eiuGuiField = $eiuEntryGuiTypeDef->field($this->defPropPath);
			}
		}
		
		if (!$required) return null;
		
		throw new EiuPerimeterException(
				'Can not create EiuField because non of the following types were provided as eiArgs: '
				. implode(', ', self::EI_FIELD_TYPES));
	}
	
	public static function buildEiuFrameFormEiArg($eiArg, string $argName = null, bool $required = false) {
		if ($eiArg instanceof EiuFrame) {
			return $eiArg;
		}
		
		if ($eiArg === null && !$required) {
			return null;
		}
		
		if ($eiArg instanceof EiFrame) {
			return new EiuFrame($eiArg, $this);
		}
		
		if ($eiArg instanceof N2nContext) {
			try {
				return new EiuFrame($eiArg->lookup(ManageState::class)->preakEiFrame(), $this);
			} catch (ManageException $e) {
				throw new EiuPerimeterException('Can not create EiuFrame in invalid context.', 0, $e);
			}
		}
		
		if ($eiArg instanceof EiuCtrl) {
			return $eiArg->frame();
		}
		
		if ($eiArg instanceof EiuEntry) {
			return $eiArg->getEiuFrame($required);
		}
		
		if ($eiArg instanceof Eiu) {
			return $eiArg->frame();
		}
		
		ArgUtils::valType($eiArg, self::EI_FRAME_TYPES, !$required, $argName);
	}
	
	/**
	 * @param mixed $eiArg
	 * @param EiuFrame $eiuFrame
	 * @param string $argName
	 * @param bool $required
	 * @return \rocket\ei\util\entry\EiuEntry|NULL
	 */
	public static function buildEiuEntryFromEiArg($eiArg, EiuFrame $eiuFrame = null, string $argName = null, bool $required = false) {
		if ($eiArg instanceof EiuEntry) {
			return $eiArg;
		}
		
		if ($eiArg !== null) {
			$eiEntry = null;
			$eiObject = self::determineEiObject($eiArg, $eiEntry);
			return (new Eiu($eiObject, $eiEntry, $eiuFrame))->entry();
		}
			
		if (!$required) {
			return null;
		}
		
		ArgUtils::valType($eiArg, self::EI_ENTRY_TYPES);
	}
	
	/**
	 * @param mixed $eiObjectObj
	 * @return \rocket\ei\manage\EiObject|null
	 */
	public static function determineEiObject($eiObjectArg, &$eiEntry = null, &$eiEntryGui = null) {
		if ($eiObjectArg instanceof EiObject) {
			return $eiObjectArg;
		} 
			
		if ($eiObjectArg instanceof EiEntry) {
			$eiEntry = $eiObjectArg;
			return $eiObjectArg->getEiObject();
		}
		
		if ($eiObjectArg instanceof EiEntityObj) {
			return new LiveEiObject($eiObjectArg);
		}
		
		if ($eiObjectArg instanceof Draft) {
			return new DraftEiObject($eiObjectArg);
		}
		
		if ($eiObjectArg instanceof EiuObject) {
			return $eiObjectArg->getEiObject();
		}
		
		if ($eiObjectArg instanceof EiuEntry) {
			$eiEntry = $eiObjectArg->getEiEntry(false);
			return $eiObjectArg->object()->getEiObject();
		}
		
		if ($eiObjectArg instanceof EiuEntryGui && null !== ($eiuEntry = $eiObjectArg->getEiuEntry(false))) {
			$eiEntry = $eiuEntry->getEiEntry(false);
			$eiEntryGui = $eiObjectArg->getEiEntryGui();
			return $eiuEntry->object()->getEiObject();
		}
		
		if ($eiObjectArg instanceof Eiu && null !== ($eiuEntry = $eiObjectArg->entry(false))) {
			return $eiuEntry->object()->getEiObject();
		}
		
		return null;
// 		if (!$required) return null;
		
// 		throw new EiuPerimeterException('Can not determine EiObject of passed argument type ' 
// 				. TypeUtils::getTypeInfo($eiObjectArg) . '. Following types are allowed: '
// 				. implode(', ', array_merge(self::EI_FRAME_TYPES, self::EI_ENTRY_TYPES)));
	}
	
	/**
	 * 
	 * @param mixed $eiTypeObj
	 * @param bool $required
	 * @throws EiuPerimeterException
	 * @return \rocket\ei\EiType|NULL
	 */
	public static function determineEiType($eiTypeArg, bool $required = false) {
		if (null !== ($eiObject = self::determineEiObject($eiTypeArg))) {
			return $eiObject->getEiEntityObj()->getEiType();
		}
		
		if ($eiTypeArg instanceof EiType) {
			return $eiTypeArg;
		}
		
		if ($eiTypeArg instanceof EiMask) {
			return $eiTypeArg->getEiEngine()->getEiMask()->getEiType();
		}
		
		if ($eiTypeArg instanceof EiFrame) {
			return $eiTypeArg->getEiEngine()->getEiMask()->getEiType();
		}
		
		if ($eiTypeArg instanceof Eiu && $eiuFrame = $eiTypeArg->frame(false)) {
			return $eiuFrame->getContextEiType();
		}
		
		if ($eiTypeArg instanceof EiuFrame) {
			return $eiTypeArg->getEiType();
		}
		
		if ($eiTypeArg instanceof EiuEntry && null !== ($eiuFrame = $eiTypeArg->getEiuFrame(false))) {
			return $eiuFrame->getContextEiType();
		}
		
		if (!$required) return null;
		
		throw new EiuPerimeterException('Can not determine EiType of passed argument type ' 
				. TypeUtils::getTypeInfo($eiTypeArg) . '. Following types are allowed: '
				. implode(', ', array_merge(self::EI_FRAME_TYPES, self::EI_ENTRY_TYPES)));
	}
	
	/**
	 * @param mixed $eiTypeArg
	 * @param Spec $spec
	 * @param string $argName
	 * @throws EiuPerimeterException
	 * @return \rocket\ei\EiType
	 */
	public static function lookupEiTypeFromEiArg($eiTypeArg, Spec $spec, string $argName = null) {
		try {
			if ($eiTypeArg instanceof \ReflectionClass) {
				return $spec->getEiTypeByClass($eiTypeArg);
			}
			
			if (!is_scalar($eiTypeArg)) {
				return self::buildEiTypeFromEiArg($eiTypeArg, $argName, true);
			}
			
			if (!$spec->containsEiTypeId($eiTypeId) && $spec->containsEiTypeClassName($eiTypeArg)) {
				return $spec->getEiTypeByClassName($eiTypeArg);
			}
			
			return $spec->getEiTypeByClassName($eiTypeArg);
		} catch (UnknownTypeException $e) {
			throw new EiuPerimeterException('Can not determine EiType of passed argument ' . $argName . ': '
					. \n2n\util\StringUtils::strOf($eiTypeArg, true), 0, $e);
		}
	}
	
	
	public static function buildEiTypesFromEiArg(?array $eiTypeArg, string $argName = null, bool $required = true) {
		if ($eiTypeArg === null) {
			return null;
		}
		
		return array_map(
				function ($eiTypeArg) use ($argName) { 
					return self::buildEiTypeFromEiArg($eiEntryArg, $argName, true); 
				}, 
				$eiTypeArg);
	}
	
	public static function buildEiTypeFromEiArg($eiTypeArg, string $argName = null, bool $required = true) {
		if ($eiTypeArg === null && !$required) {
			return null;
		}
		
		if (null !== ($eiType = self::determineEiType($eiTypeArg))) {
			return $eiType;
		}
		
		throw new EiuPerimeterException('Can not determine EiType of passed argument ' . $argName 
				. '. Following types are allowed: '
				. implode(', ', array_merge(self::EI_FRAME_TYPES, self::EI_ENTRY_TYPES)) . '; '
				. TypeUtils::getTypeInfo($eiTypeArg) . ' given.');
	}
	
	/**
	 * @param mixed $eiEntryArg
	 * @param string $argName
	 * @param bool $required
	 * @throws EiuPerimeterException
	 * @return \rocket\ei\manage\entry\EiEntry
	 */
	public static function buildEiEntryFromEiArg($eiEntryArg, string $argName = null, bool $required = true) {
		if ($eiEntryArg instanceof EiEntry) {
			return $eiEntryArg;
		}
		
		if ($eiEntryArg instanceof EiuEntry) {
			return $eiEntryArg->getEiEntry($required);
		}
		
		throw new EiuPerimeterException('Can not determine EiEntry of passed argument ' . $argName
				. '. Following types are allowed: '
				. implode(', ', array_merge(self::EI_ENTRY_TYPES)) . '; '
				. TypeUtils::getTypeInfo($eiEntryArg) . ' given.');
	}
	
	/**
	 * 
	 * @param mixed $eiEntryGuiArg
	 * @param string $argName
	 * @param bool $required
	 * @throws EiuPerimeterException
	 * @return \rocket\ei\manage\gui\EiEntryGui
	 */
	public static function buildEiEntryGuiFromEiArg($eiEntryGuiArg, string $argName = null, bool $required = true) {
		if ($eiEntryGuiArg instanceof EiEntryGui) {
			return $eiEntryGuiArg;
		}
		
		if ($eiEntryGuiArg instanceof EiuEntryGui) {
			return $eiEntryGuiArg->getEiEntryGui();
		}
		
		if ($eiEntryGuiArg instanceof EiuGuiFrame) {
			$eiEntryGuiArg = $eiEntryGuiArg->getEiGuiFrame();
		}
		
		if ($eiEntryGuiArg instanceof EiGuiFrame) {
			$eiEntryGuis = $eiEntryGuiArg->getEiEntryGuis();
			if (1 == count($eiEntryGuiArg)) {
				return current($eiEntryGuis);
			}
			
			throw new EiuPerimeterException('Can not determine EiEntryGui of passed EiGuiFrame ' . $argName);
		}
		
		throw new EiuPerimeterException('Can not determine EiEntryGui of passed argument ' . $argName
				. '. Following types are allowed: ' . implode(', ', array_merge(self::EI_ENTRY_GUI_TYPES)) . '; '
				. TypeUtils::getTypeInfo($eiEntryGuiArg) . ' given.');
	}
	
	public static function buildEiGuiFrameFromEiArg($eiGuiFrameArg, string $argName = null, bool $required = true) {
		if ($eiGuiFrameArg instanceof EiGuiFrame) {
			return $eiGuiFrameArg;
		}
	
		if ($eiGuiFrameArg instanceof EiuGuiFrame) {
			return $eiGuiFrameArg->getEiGuiFrame();
		}
		
		if ($eiGuiFrameArg instanceof EiEntryGui) {
			return $eiGuiFrameArg->getEiGuiFrame();
		}
	
		if ($eiGuiFrameArg instanceof EiuEntryGui) {
			return $eiGuiFrameArg->getEiGuiFrame();
		}
		
		if ($eiGuiFrameArg instanceof Eiu && null !== ($eiuGuiFrame = $eiGuiFrameArg->guiFrame(false))) {
			return $eiuGuiFrame->getEiGuiFrame();
		}
	
		throw new EiuPerimeterException('Can not determine EiGuiFrame of passed argument ' . $argName
				. '. Following types are allowed: '
				. implode(', ', array_merge(self::EI_GUI_TYPES)) . '; '
				. TypeUtils::getTypeInfo($eiGuiFrameArg) . ' given.');
	}
	
	public static function buildEiObjectFromEiArg($eiObjectObj, string $argName = null, EiType $eiType = null, 
			bool $required = true, &$eiEntry = null, &$eiGuiFrameArg = null) {
		if (!$required && $eiObjectObj === null) {
			return null;
		}
		
		$eiEntryGui = null;
		if (null !== ($eiObject = self::determineEiObject($eiObjectObj, $eiEntry, $eiEntryGui))) {
			return $eiObject;
		}
		
		$eiObjectTypes = self::EI_ENTRY_TYPES;
		
		if ($eiType !== null) {
			$eiObjectTypes[] = $eiType->getEntityModel()->getClass()->getName();
			try {
				return LiveEiObject::create($eiType, $eiObjectObj);
			} catch (\InvalidArgumentException $e) {
			}
		}
		
		ArgUtils::valType($eiObjectObj, $eiObjectTypes, !$required, $argName);
		throw new IllegalStateException();
	}
	
	public static function buildEiFrameFromEiArg($eiFrameObj, string $argName = null, bool $required = true) {
		if (!$required && $eiFrameObj === null) {
			return null;
		}
				
		if ($eiFrameObj instanceof EiFrame) {
			return $eiFrameObj;
		}
		
		if ($eiFrameObj instanceof EiuFrame) {
			return $eiFrameObj->getEiFrame();
		}
				
		ArgUtils::valType($eiFrameObj, [EiFrame::class, EiuFrame::class], !$required, $argName);
		throw new \LogicException();
	}
}