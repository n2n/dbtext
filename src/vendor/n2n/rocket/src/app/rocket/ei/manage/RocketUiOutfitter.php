<?php

namespace rocket\ei\manage;

use n2n\impl\web\ui\view\html\HtmlElement;
use n2n\impl\web\ui\view\html\HtmlUtils;
use n2n\impl\web\ui\view\html\HtmlView;
use n2n\web\dispatch\mag\MagCollection;
use n2n\web\dispatch\mag\UiOutfitter;
use n2n\web\dispatch\map\PropertyPath;
use n2n\web\ui\UiComponent;
use n2n\impl\web\ui\view\html\HtmlSnippet;

class RocketUiOutfitter implements UiOutfitter {
	/**
	 * @param string $nature
	 * @return array
	 */
	public function createAttrs(int $nature): array {
		$attrs = array();
		if ($nature & self::NATURE_MAIN_CONTROL) {
			$newAttrs = ($nature & self::NATURE_CHECK) ? array('class' => 'form-check-input') : array('class' => 'form-control');
			$attrs = HtmlUtils::mergeAttrs($newAttrs, $attrs);
		}
		

		if ($nature & self::NATURE_CHECK_LABEL) {
			$attrs = HtmlUtils::mergeAttrs(array('class' => 'form-check-label'), $attrs);
		}

		if ($nature & self::NATURE_BTN_PRIMARY) {
			$attrs = HtmlUtils::mergeAttrs(array('class' => 'btn btn-primary mt-2'), $attrs);
		}

		if ($nature & self::NATURE_BTN_SECONDARY) {
			$attrs = HtmlUtils::mergeAttrs(array('class' => 'btn btn-secondary'), $attrs);
		}

		if ($nature & self::NATURE_BTN_FULL_WIDTH) {
			$attrs = HtmlUtils::mergeAttrs(array('class' => 'btn-block'), $attrs);
		}

		if ($nature & self::NATURE_MASSIVE_ARRAY_ITEM_STRUCTURE) {
			$attrs = HtmlUtils::mergeAttrs(array('class' => 'rocket-impl-entry rocket-structure-element'), $attrs);
		}

		if ($nature & self::NATURE_CONTROL_WRAPPER) {
			$attrs = HtmlUtils::mergeAttrs(array('class' => 'rocket-structure-content'), $attrs);
		}
		
		if ($nature & self::NATURE_CONTROL_GROUP) {
			$attrs = HtmlUtils::mergeAttrs(array('class' => 'input-group'), $attrs);
		}
		
		if ($nature & self::NATURE_CONTROL_GROUP_ADDON) {
			$attrs = HtmlUtils::mergeAttrs(array('class' => 'input-group-append'), $attrs);
		}
		
		return $attrs;
	}

	/**
	 * @param int $elemNature
	 * @param array|null $attrs
	 * @param null $contents
	 * @return HtmlElement
	 */
	public function createElement(int $elemNature, array $attrs = null, $contents = ''): UiComponent {
		if ($attrs === null) {
			$attrs = array();
		}

		if ($elemNature & self::EL_NATRUE_CONTROL_ADDON_SUFFIX_WRAPPER) {
			return new HtmlElement('div', HtmlUtils::mergeAttrs(array('class' => 'input-group'), $attrs), $contents);
		}

		if ($elemNature & self::EL_NATURE_CONTROL_ADDON_WRAPPER) {
			$inputGroupAppend = new HtmlElement('span', array('class' => 'input-group-text'), $contents);
			return new HtmlElement('div', HtmlUtils::mergeAttrs(array('class' => 'input-group-append'), $attrs), $inputGroupAppend);
		}

		if ($elemNature & self::NATURE_MASSIVE_ARRAY_ITEM && $elemNature & self::NATURE_MASSIVE_ARRAY_ITEM_CONTROL) {
			$container = new HtmlElement('div', array('class' => 'rocket-impl-entry rocket-structure-element'), '');

			$container->appendLn(new HtmlElement('div', array('class' => 'col-auto'), $contents));
			$container->appendLn(new HtmlElement('div', array('class' => 'col-auto mag-collection-control-wrapper'), ''));

			return $container;
		}

		if ($elemNature & self::EL_NATURE_CONTROL_ADD) {
			return new HtmlElement('div', array('class' => 'rocket-add-paste'),
					new HtmlElement('button', HtmlUtils::mergeAttrs(
					$this->createAttrs(UiOutfitter::NATURE_BTN_SECONDARY + UiOutfitter::NATURE_BTN_FULL_WIDTH), $attrs), $contents));
		}

		if ($elemNature & self::EL_NATURE_CONTROL_REMOVE) {
			return new HtmlElement('button', HtmlUtils::mergeAttrs(
				$this->createAttrs(UiOutfitter::NATURE_BTN_SECONDARY), $attrs),
				new HtmlElement('i', array('class' => UiOutfitter::ICON_NATURE_REMOVE), ''));
		}


		if ($elemNature & self::EL_NATURE_ARRAY_ITEM_CONTROL) {
			$summary = new HtmlElement('div', array('class' => 'rocket-summary'), '');

			$container = new HtmlElement('div', HtmlUtils::mergeAttrs($attrs, $this->createAttrs(UiOutfitter::NATURE_MASSIVE_ARRAY_ITEM_STRUCTURE)), $summary);

			$summary->appendLn(new HtmlElement('div', array('class' => 'rocket-handle'), ''));
			$summary->appendLn(new HtmlElement('div', array('class' => 'rocket-impl-content'), $contents));
			$summary->appendLn(new HtmlElement('div',
				array('class' => 'rocket-simple-commands ' . MagCollection::CONTROL_WRAPPER_CLASS),
				$this->createElement(UiOutfitter::EL_NATURE_CONTROL_REMOVE, array('class' => MagCollection::CONTROL_REMOVE_CLASS), '')));

			return $container;
		}

		if ($elemNature & self::EL_NATURE_CHECK_WRAPPER) {
			return new HtmlElement('div', array('class' => 'form-check'), $contents);
		}

		return new HtmlSnippet($contents);
	}

	public function createMagDispatchableView(PropertyPath $propertyPath = null, HtmlView $contextView): UiComponent {
		return $contextView->getImport('\rocket\ei\manage\gui\view\magForm.html',
			array('propertyPath' => $propertyPath, 'uo' => $this));
	}
}