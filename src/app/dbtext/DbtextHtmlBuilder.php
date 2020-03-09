<?php
namespace dbtext;

use dbtext\model\DbtextService;
use n2n\impl\web\ui\view\html\HtmlView;
use n2n\web\ui\UiComponent;
use n2n\impl\web\ui\view\html\HtmlBuilderMeta;
use n2n\web\ui\Raw;
use n2n\l10n\N2nLocale;

/**
 * <p>Use this html builder for easy access to {@see TextService} in html views.</p>
 *
 * <p><strong>Example usage</strong></p>
 * <pre>
 *	&lt;?php
 *		use dbtext\DbtextHtmlBuilder;
 *
 *		$dbtextHtml = new DbtextHtmlBuilder($view);
 *	?&gt;
 *	&lt;p&gt;
 *		&lt;?php $dbtextHtml-&gt;t('greetings') ?&gt;
 *	&lt;/p&gt;
 * </pre>
 */
class DbtextHtmlBuilder {
	/**
	 * @var HtmlView $view
	 */
	private $view;

	/**
	 * @var DbtextService $textService
	 */
	private $textService;

	/**
	 * @var DbtextHtmlBuilderMeta
	 */
	private $meta;
	
	/**
	 * @param HtmlView $view
	 * @param string[] $namespaces
	 * @param N2nLocale[] $n2nLocales
	 */
	public function __construct(HtmlView $view, array $namespaces = array(), array $n2nLocales = array()) {
		$this->view = $view;
		$this->textService = $view->lookup(DbtextService::class);
		
		if (empty($namespaces)) {
			$namespaces[] = $this->view->getModuleNamespace();
		}
		
		if (empty($n2nLocales)) {
			$n2nLocales[] = $this->view->getN2nLocale();
		}
		
		$this->meta = new DbtextHtmlBuilderMeta($namespaces, $n2nLocales);
	}

	/**
	 * Outputs {@see self::getT()}
	 * Use as follows
	 * 
	 * @param string $key
	 * @param array $args
	 */
	public function t(string $key, array $args = null, array $replacements = null, string ...$namespaces) {
		$this->view->getHtmlBuilder()->out($this->getT($key, $args, $replacements, ...$namespaces));
	}

	/**
	 * Uses {@see TextService::t()} to return translated text as an {@see UiComponent}.
	 *
	 * &lt;p&gt;
	 * &lt;?php $textHtml-&gt;t('intro_text') ?&gt;
	 * &lt;/p&gt;
	 *
	 * @param string $key
	 * @param array|null $args
	 * @param array|null $replacements
	 * @param string[] ...$namespaces
	 * @return Raw
	 */
	public function getT(string $key, array $args = null, array $replacements = null, string ...$namespaces) {
		if (empty($namespaces)) {
			$namespaces = $this->meta->getNamespaces();
		}

		$translatedText = $this->textService->t($namespaces, $key, $args, ...$this->meta->getN2nLocales());
		$replacedText = HtmlBuilderMeta::replace($translatedText, $replacements, $this->view);
		return new Raw($replacedText);
	}

	/**
	 * Use for easy access to a textblock formatted by printf rules.
	 *
	 * &lt;p&gt;
	 * &lt;?php $textHtml-&gt;tf('intro_text') ?&gt;
	 * &lt;/p&gt;
	 * 
	 * @param string $key
	 * @param string[] $args
	 * @param string[] $replacements
	 * @param string ...$namespaces
	 */
	public function tf(string $key, array $args = null, array $replacements = null, string ...$namespaces) {
		$this->view->out($this->getTf($key, $args, $replacements, ...$namespaces));
	}

	/**
	 * Use for easy access to a UiComponent with textblock formatted by printf rules.
	 *
	 * @param string $key
	 * @param string[] $args
	 * @param string[] $replacements
	 * @param string ...$namespaces
	 * @return UiComponent
	 */
	public function getTf(string $key, array $args = null, array $replacements = null, string ...$namespaces) {
		if (empty($namespaces)) {
			$namespaces = $this->meta->getNamespaces();
		}
		
		$translatedText = $this->textService->tf($namespaces, $key, $args, ...$this->meta->getN2nLocales());
		$replacedText = HtmlBuilderMeta::replace($translatedText, $replacements, $this->view);
		return new Raw($replacedText);
	}
	
	/**
	 * @return \dbtext\DbtextHtmlBuilderMeta
	 */
	public function meta() {
		return $this->meta;
	}
}
