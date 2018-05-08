<?php
namespace dbtext;

use dbtext\text\Text;
use n2n\impl\web\ui\view\html\HtmlView;
use n2n\reflection\CastUtils;
use n2n\web\ui\UiComponent;
use rocket\ei\util\model\Eiu;
use rocket\impl\ei\component\prop\adapter\DisplayableEiPropAdapter;

class PlaceholderEiProp extends DisplayableEiPropAdapter {
	/**
	 * @param HtmlView $view
	 * @param Eiu $eiu
	 * @return UiComponent
	 */
	public function createOutputUiComponent(HtmlView $view, Eiu $eiu) {
		$text = $eiu->entry()->getEntityObj();
		CastUtils::assertTrue($text instanceof Text);

		$placeholderTexts = array();
		foreach ($text->getPlaceholders() as $placeholderName => $value) {
			$placeholderTexts[] = $placeholderName . '("' . $value . '")';
		}

		return implode(', ', $placeholderTexts);
	}
}