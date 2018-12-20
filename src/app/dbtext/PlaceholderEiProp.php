<?php
namespace dbtext;

use dbtext\text\Text;
use n2n\impl\web\ui\view\html\HtmlElement;
use n2n\impl\web\ui\view\html\HtmlView;
use n2n\l10n\TextCollection;
use n2n\reflection\CastUtils;
use n2n\web\ui\Raw;
use n2n\web\ui\UiComponent;
use rocket\ei\util\Eiu;
use rocket\impl\ei\component\prop\adapter\DisplayableEiPropAdapter;

class PlaceholderEiProp extends DisplayableEiPropAdapter {
	/**
	 * @param HtmlView $view
	 * @param Eiu $eiu
	 * @return UiComponent
	 */
	public function createUiComponent(HtmlView $view, Eiu $eiu) {
		$dtc = $eiu->dtc('dbtext');
		$text = $eiu->entry()->getEntityObj();
		CastUtils::assertTrue($text instanceof Text);

		$placeholders = $text->getPlaceholders();

		if ($placeholders === null || count($placeholders) === 0) {
			return new Raw($dtc->t('dbtext_no_placeholders_text'));
		}

		if ($eiu->gui()->isCompact()) {
			return implode(', ', array_keys($placeholders));
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

		return $placeholderDiv;
	}
}