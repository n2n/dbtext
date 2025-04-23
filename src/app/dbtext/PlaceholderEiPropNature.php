<?php
namespace dbtext;

use dbtext\text\Text;
use n2n\impl\web\ui\view\html\HtmlElement;
use n2n\l10n\TextCollection;
use n2n\util\type\CastUtils;
use rocket\impl\ei\component\prop\adapter\DisplayableEiPropNatureAdapter;
use rocket\ui\si\content\impl\SiFields;
use rocket\ui\gui\field\BackableGuiField;
use rocket\ui\gui\field\impl\GuiFields;
use rocket\op\ei\util\Eiu;
use rocket\ui\si\content\impl\meta\SiCrumb;

class PlaceholderEiPropNature extends DisplayableEiPropNatureAdapter {
	
	protected function prepare() {
	}

	public function buildOutGuiField(Eiu $eiu): ?BackableGuiField {
		$dtc = $eiu->dtc('dbtext');
		$text = $eiu->entry()->getEntityObj();
		CastUtils::assertTrue($text instanceof Text);

		$placeholders = $text->getPlaceholders();

		if ($placeholders === null || count($placeholders) === 0) {
			return GuiFields::out(SiFields::crumbOut(
					SiCrumb::createLabel($dtc->t('dbtext_no_placeholders_text'))));
		}

		if ($eiu->guiDefinition()->isCompact()) {
			return GuiFields::out(SiFields::crumbOut(
					SiCrumb::createLabel(implode(', ', array_keys($placeholders)))));

		}

		$placeholderDiv = new HtmlElement('div');

		foreach ($placeholders as $placeholderName => $placeholderValue) {
			$placeholderList = new HtmlElement('dl', array('class' => 'row mb-0'));
			$placeholderList->appendLn(new HtmlElement('dt', array('class' => 'col'),
					TextCollection::ARG_PREFIX . $placeholderName . TextCollection::ARG_SUFFIX));
			$placeholderList->appendLn(new HtmlElement('dd', array('class' => 'col-9'),
					$placeholderValue . ', ...'));
			$placeholderDiv->appendLn($placeholderList);
		}

		$helperDiv = new HtmlElement('div', array('class' => 'alert alert-dark mt-3'));
		$helperDiv->appendLn(new HtmlElement('h4', null, $dtc->t('dbtext_placeholder_info_title')));
		$helperDiv->appendLn(new HtmlElement('p', null, $dtc->t('dbtext_placeholder_info_text')));

		$placeholderDiv->appendLn($helperDiv);

		return GuiFields::out(SiFields::iframeOut($placeholderDiv, $eiu->getN2nContext()));
	}

}