<?php
namespace dbtext;

use dbtext\model\TextService;
use n2n\impl\web\ui\view\html\HtmlView;
use n2n\web\ui\UiComponent;
use n2n\impl\web\ui\view\html\HtmlBuilderMeta;
use n2n\web\ui\Raw;

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
 *		&lt;?php $dbtextHtml-&gt;text('greetings') ?&gt;
 *	&lt;/p&gt;
 * </pre>
 */
class DbtextHtmlBuilder {
	/**
	 * @var HtmlView $view
	 */
	private $view;

	/**
	 * @var TextService $textService
	 */
	private $textService;

	/**
	 * @var DbtextHtmlBuilderMeta
	 */
	private $meta;

	/**
	 * 
	 * @var string
	 */
	private $namespace;
	
	public function __construct(HtmlView $view, array $namespaces = array(), array $n2nLocales = array()) {
		$this->view = $view;
		$this->textService = $view->lookup(TextService::class);
		$this->namespace = $namespace ?? $this->view->getModuleNamespace();
		$this->meta = new DbtextHtmlBuilderMeta($namespaces, $n2nLocales);
	}

	/**
	 * Outputs {@see self::getT()}
	 *
	 * @param string $key
	 * @param array $args
	 */
	public function t(string $key, array $args = null, array $replacements = null, string $namespace = null) {
		$this->view->out($this->getT($key, $args, $replacements, $namespace));
	}

	/**
	 * Uses {@see DbtextCollection::t()} to determine the correct translation and returns it as an {@see UiComponent}.
	 *
	 * @param string $key
	 * @param array $args
	 * @return UiComponent
	 */
	public function getT(string $key, array $args = null, array $replacements = null, string $namespace = null) {
		$translatedText = $this->textService->t($namespace ?? $this->namespace, $key, $args, $this->view->getN2nLocale());
		$replacedText = HtmlBuilderMeta::replace($translatedText, $replacements, $this->view);
		return new Raw($replacedText);
	}

	/**
	 * Outputs {@see self::getTf()}
	 *
	 * @param string $key
	 * @param array $args
	 */
	public function tf(string $key, array $args = null, array $replacements = null, string $namespace = null) {
		$this->view->out($this->getTf($key, $args, $replacements, $namespace));
	}

	/**
	 * Uses {@see DbtextCollection::tf()} to determine the correct translation and returns it as an {@see UiComponent}.
	 *
	 * @param string $key
	 * @param array $args
	 * @return UiComponent
	 */
	public function getTf(string $key, array $args = null, array $replacements = null, string $namespace = null) {
		$translatedText = $this->textService->tf($namespace ?? $this->namespace, $key, $args, $this->view->getN2nLocale());
		$replacedText = HtmlBuilderMeta::replace($translatedText, $textHtml, $this->view);
		return new Raw($replacedText);
	}
}