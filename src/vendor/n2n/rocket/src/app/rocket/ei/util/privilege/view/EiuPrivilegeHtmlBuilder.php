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
namespace rocket\ei\util\privilege\view;

use n2n\impl\web\ui\view\html\HtmlView;
use rocket\ei\manage\security\privilege\PrivilegeDefinition;
use n2n\web\ui\UiComponent;
use n2n\impl\web\ui\view\html\HtmlElement;
use n2n\web\ui\Raw;
use n2n\web\dispatch\map\PropertyPath;
use rocket\ei\EiCommandPath;

class EiuPrivilegeHtmlBuilder {
	private $view;
	
	public function __construct(HtmlView $view) {
		$this->view = $view;
	}
	
	public function privilegeCheckboxes($propertyExpression, PrivilegeDefinition $privilegeDefinition) {
		$this->view->out($this->getPrivilegeCheckboxes($propertyExpression, $privilegeDefinition));
	}
	
	public function getPrivilegeCheckboxes($propertyExpression, PrivilegeDefinition $privilegeDefinition): UiComponent {
		return $this->buildPrivilegeUl($privilegeDefinition->getEiCommandPrivileges(), new EiCommandPath(array()), 
				$this->view->getFormHtmlBuilder()->meta()->createPropertyPath($propertyExpression));
	}
	
	private function buildPrivilegeUl(array $eiCommandPrivileges, EiCommandPath $baseEiCommandPath, PropertyPath $propertyPath) {
		if (empty($eiCommandPrivileges)) return new Raw('');
		
		$formHtml = $this->view->getFormHtmlBuilder();
		$n2nLocale = $this->view->getN2nContext()->getN2nLocale();
		
		$ulElement = new HtmlElement('ul');
		
		if ($baseEiCommandPath->isEmpty()) {
			$ulElement->appendLn(new HtmlElement('li', null, new Raw(
					'<input type="checkbox" disabled="disabled" checked="checked" /> <label>' 
							. $this->view->getHtmlBuilder()->getL10nText('user_privilege_read_label') 
							. '</label>')));
		}
		
		foreach ($eiCommandPrivileges as $commandPathStr => $eiCommandPrivilege) {
			$raw = new Raw();
			
			$commandPath = $baseEiCommandPath->ext($commandPathStr);
			
			$raw->appendLn($formHtml->getInputCheckbox($propertyPath, (string) $commandPath, 
					array(), $eiCommandPrivilege->getLabel($n2nLocale)));
			$raw->appendLn($this->buildPrivilegeUl($eiCommandPrivilege->getSubEiCommandPrivileges(), $commandPath, $propertyPath));
			
			$ulElement->appendContent(new HtmlElement('li', null, $raw));
		}
		
		return $ulElement;
	}
	
}
