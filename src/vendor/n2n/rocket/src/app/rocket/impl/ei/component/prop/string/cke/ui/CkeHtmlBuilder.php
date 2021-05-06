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
namespace rocket\impl\ei\component\prop\string\cke\ui;

use n2n\impl\web\ui\view\html\HtmlElement;
use n2n\util\StringUtils;
use n2n\impl\web\ui\view\html\HtmlView;
use n2n\web\ui\Raw;
use n2n\l10n\N2nLocale;
use n2n\util\uri\Url;
use rocket\impl\ei\component\prop\string\cke\model\CkeCssConfig;
use rocket\impl\ei\component\prop\string\cke\model\CkeUtils;
use n2n\util\uri\UnavailableUrlException;
use n2n\util\type\CastUtils;
use rocket\impl\ei\component\prop\string\cke\ui\CkeConfig;
use rocket\impl\ei\component\prop\string\cke\model\CkeStyle;
use n2n\util\type\ArgUtils;
use n2n\web\ui\UiComponent;
use rocket\impl\ei\component\prop\string\cke\model\CkeLinkProvider;
use n2n\impl\web\ui\view\html\HtmlUtils;

class CkeHtmlBuilder {

	private $html;
	private $view;

	private $linkProviders = array();

	public function __construct(HtmlView $view) {
		$this->view = $view;
		$this->html = $view->getHtmlBuilder();
	}
	
	/**
	 * @param string|UiComponent|null $contentsHtml
	 * @param N2nLocale|null $n2nLocale
	 */
	public function out($contentsHtml = null, N2nLocale $n2nLocale = null) {
		$this->view->out($this->getOut($contentsHtml, $n2nLocale));
	}

	/**
	 * @param string|UiComponent|null $contentsHtml
	 * @param N2nLocale|null $n2nLocale
	 * @return \n2n\web\ui\Raw
	 */
	public function getOut($contentsHtml = null, N2nLocale $n2nLocale = null) {
		ArgUtils::valType($contentsHtml, array('string', UiComponent::class), true, 'contentsHtml');
		$n2nLocale = $n2nLocale ?? $this->view->getN2nLocale();
		$that = $this;
		
		$contentsHtml = $this->view->getOut($contentsHtml);

		return new Raw(preg_replace_callback('/(href\s*=\s*")?\s*(ckelink:\?provider=[^"<]+&amp;key=[^"<]+)(")?/',
				function($matches) use ($that, $n2nLocale) {
					$url = null;
					try {
						$url = Url::create(htmlspecialchars_decode($matches[2]), true);
					} catch (\InvalidArgumentException $e) {
						return '';
					}

					$query = $url->getQuery()->toArray();
					$ckeLinkProvider = null;
					if (!isset($query['provider']) || !isset($query['key'])) {
						return '';
					}

					$ckeLinkProvider = $that->lookupLinkProvider($query['provider']);
					if ($ckeLinkProvider === null) {
						return '';
					}

					try {
						$url = $ckeLinkProvider->buildUrl($query['key'], $that->view, $n2nLocale);
					} catch (UnavailableUrlException $e) {
						return '';
					}

					if ($url === null) {
						$url = $query['key'];
					}

					return $matches[1] .  $url . ($matches[3] ?? '');
				}, $contentsHtml));
	}
	
		
	/**
	 * @param mixed $propertyPath
	 * @param CkeComposer $ckeComposer
	 * @param CkeCssConfig $ckeCssConfig
	 * @param CkeCssConfig[] $linkProviders
	 */
	public function editor($propertyPath = null, CkeComposer $ckeComposer = null, CkeCssConfig $ckeCssConfig = null,
			array $linkProviders = array(), array $attrs = null) {
		$this->view->out($this->getEditor($propertyPath, $ckeComposer, $ckeCssConfig, $linkProviders, $attrs));
	}

	/**
	 * @param mixed $propertyPath
	 * @param CkeComposer $ckeComposer
	 * @param CkeCssConfig $ckeCssConfig
	 * @param CkeCssConfig[] $linkProviders
	 * @return \n2n\impl\web\ui\view\html\HtmlElement
	 */
	public function getEditor($propertyExpression = null, CkeComposer $ckeComposer = null,
			CkeCssConfig $ckeCssConfig = null, array $linkProviders = array(), array $attrs = null) {
		$this->html->meta()->addLibrary(new CkeLibrary());

		$attrs = HtmlUtils::mergeAttrs(array('class' => 'rocket-impl-cke-classic',
				'data-rocket-impl-toolbar' => StringUtils::jsonEncode($this->buildEditorAttrs($ckeComposer, $ckeCssConfig)),
				'data-link-configurations' => StringUtils::jsonEncode($this->buildLinkConfigData($linkProviders))), (array) $attrs);


		return $this->view->getFormHtmlBuilder()->getTextarea($propertyExpression, $attrs);
	}

	public function getTextarea(string $value = null, CkeComposer $ckeComposer = null,
			CkeCssConfig $ckeCssConfig = null, array $linkProviders = array(), array $attrs = null) {
		$this->html->meta()->addLibrary(new CkeLibrary());

		$attrs = HtmlUtils::mergeAttrs(array('class' => 'rocket-impl-cke-classic',
			'data-rocket-impl-toolbar' => StringUtils::jsonEncode($this->buildEditorAttrs($ckeComposer, $ckeCssConfig)),
			'data-link-configurations' => StringUtils::jsonEncode($this->buildLinkConfigData($linkProviders))), (array) $attrs);

		return new HtmlElement('textarea', $attrs, $value);
	}

	/**
	 * @param string|UiComponent|null $contentsHtml
	 * @param CkeCssConfig|null $ckeCssConfig
	 * @param CkeLinkProvider[] $linkProviders
	 */
	public function iframe($contentsHtml = null, CkeCssConfig $ckeCssConfig = null, array $linkProviders = null) {
		$this->view->out($this->getIframe($contentsHtml, $ckeCssConfig, $linkProviders));
	}
	
	/**
	 * @param string|UiComponent|null $contentsHtml
	 * @param CkeCssConfig|null $ckeCssConfig
	 * @param CkeLinkProvider[] $linkProviders
	 * @return \n2n\web\ui\Raw
	 */
	public function getIframe($contentsHtml = null, CkeCssConfig $ckeCssConfig = null, array $linkProviders = null) {
		ArgUtils::valType($contentsHtml, array('string', UiComponent::class), true, 'contentsHtml');
		ArgUtils::valArray($linkProviders, CkeLinkProvider::class, true, 'linkProviders');
		
		$this->linkProviders = $linkProviders;
		
		$headLinkHtml = '';
		$bodyIdHtml = '';
		$bodyClassHtml = '';
		
		if (null !== $ckeCssConfig) {
			$contentCssUrls = $this->getContentCssUrls($ckeCssConfig);
			if (!empty($contentCssUrls)) {
				$headLinkHtml = str_replace('"', '\'', StringUtils::jsonEncode($this->getContentCssUrls($ckeCssConfig)));
			}
			
			$bodyId = $ckeCssConfig->getBodyId();
			if (!empty($bodyId)) {
				$bodyIdHtml = 'data-body-id="' . $bodyId . '"';
			}
			
			$bodyClass = $ckeCssConfig->getBodyClass();
			if (!empty($bodyClass)) {
				$bodyClassHtml = 'data-body-class="' . $bodyClass . '"';
			}
		}

		$contentsHtml = htmlspecialchars(str_replace('"', "'", $this->getOut($contentsHtml, $this->view->getN2nLocale())));
		$this->html->meta()->addJs('impl/js/cke.js', 'rocket', true);
		
		return new Raw('<iframe scrolling="auto" ' . $bodyIdHtml . ' class="rocket-cke-detail" ' . $bodyClassHtml
				. 'data-contents-css="' . $headLinkHtml . '" data-content-html-json="' . $this->view->getOut($contentsHtml) . '"></iframe>');
	}

	private function buildEditorAttrs(CkeComposer $ckeComposer = null, CkeCssConfig $ckeCssConfig = null) {
		$ckeConfig = ($ckeComposer !== null) ? $ckeComposer->toCkeConfig() : $ckeConfig = CkeConfig::createDefault();

		$attrs = array('mode' => $ckeConfig->getMode(),
			'tableEditing' => $ckeConfig->isTablesEnabled(),
			'bbcode' => $ckeConfig->isBbcodeEnabled());
		if ($ckeCssConfig == null) return $attrs;

		if (!empty($bodyId = $ckeCssConfig->getBodyId())) {
			$attrs['bodyId'] = $bodyId;
		}

		if (!empty($bodyClass = $ckeCssConfig->getBodyClass())) {
			$attrs['bodyClass'] = $bodyClass;
		}

		$contentCssUrls = $this->getContentCssUrls($ckeCssConfig);
		if (!empty($contentCssUrls)) {
			$attrs['contentsCss'] = $contentCssUrls;
		}

		$ckeStyles = $ckeCssConfig->getAdditionalStyles();
		if (!empty($ckeStyles)) {
			ArgUtils::valArrayReturn($ckeStyles, $ckeCssConfig, 'getAdditionalStyles', CkeStyle::class);
			$attrs['additionalStyles'] = $this->prepareAdditionalStyles($ckeStyles);
		}

		$formatTags = $ckeCssConfig->getFormatTags();
		if (!empty($formatTags)) {
			ArgUtils::valArrayReturn($formatTags, $ckeCssConfig, 'getFormatTags', 'string');
			$attrs['formatTags'] = implode(';', $formatTags);
		}

		return $attrs;
	}
	
	private function getContentCssUrls(CkeCssConfig $ckeCssConfig) {
		$contentCssUrls = $ckeCssConfig->getContentCssUrls($this->view);
		if (empty($contentCssUrls)) return [];

		ArgUtils::valArrayReturn($contentCssUrls, $ckeCssConfig, 'getContentCssUrls', Url::class);

		return array_map(function(Url $contentCssUrl) {
			if ($contentCssUrl->isRelative()) {
				$contentCssUrl = $this->view->getRequest()->getHostUrl()->ext($contentCssUrl);
			}
			return (string) $contentCssUrl;
		}, $contentCssUrls);
	}

	private function buildLinkConfigData(array $ckeLinkProviders, N2nLocale $linkN2nLocale = null) {
		$linkN2nLocale = (null !== $linkN2nLocale) ? $linkN2nLocale : $this->view->getN2nLocale();
		$linkConfigData = array();
		foreach ($ckeLinkProviders as $providerName => $ckeLinkProvider) {
			CastUtils::assertTrue($ckeLinkProvider instanceof CkeLinkProvider);
			$title = $ckeLinkProvider->getTitle();
			$linkConfigData[$title] = array();
			$linkConfigData[$title]['items'] = array();
			$linkConfigData[$title]['open-in-new-window'] = $ckeLinkProvider->isOpenInNewWindow();
			foreach ($ckeLinkProvider->getLinkOptions($linkN2nLocale) as $key => $label) {
				$url = (new Url('ckelink'))->chQuery(array('provider' => $providerName, 'key' => $key));
				$linkConfigData[$title]['items'][] = array($label, (string) $url);
			}
		}
		return $linkConfigData;
	}

	private function lookupLinkProvider(string $lookupId) {
		if (array_key_exists($lookupId, $this->linkProviders)) {
			return $this->linkProviders[$lookupId];
		}

		try {
			return $this->linkProviders[$lookupId] = CkeUtils::lookupCkeLinkProvider($lookupId, $this->view->getN2nContext());
		} catch (\InvalidArgumentException $e) {
			return $this->linkProviders[$lookupId] = null;
		}
	}

	private function prepareAdditionalStyles(array $additionalStyles = null) {
		if (empty($additionalStyles)) return [];
		
		$encodable = array();
		foreach ($additionalStyles as $style) {
			CastUtils::assertTrue($style instanceof CkeStyle);
			$encodable[] = $style->getValueForJsonEncode();
		}
		return $encodable;
	}
}
