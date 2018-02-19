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

	public function __construct(HtmlView $view) {
		$this->view = $view;
		$this->textService = $view->lookup(TextService::class);
	}

	/**
	 * Outputs {@see self::getT()}
	 *
	 * @param string $id
	 * @param array $args
	 */
	public function t(string $id, array $args = null, array $replacements = null) {
		$this->view->out($this->getT($id, $args, $replacements));
	}

	/**
	 * Uses {@see DbtextCollection::t()} to determine the correct translation and returns it as an {@see UiComponent}.
	 *
	 * @param string $id
	 * @param array $args
	 * @return UiComponent
	 */
	public function getT(string $id, array $args = null, array $replacements = null) {
		$translatedText = $this->textService->t($this->view->getModuleNamespace(), $id, $args, $this->view->getN2nLocale());
		$replacedText = HtmlBuilderMeta::replace($translatedText, $replacements, $this->view);
		return new Raw($replacedText);
	}

	/**
	 * Outputs {@see self::getTf()}
	 *
	 * @param string $id
	 * @param array $args
	 */
	public function tf(string $id, array $args = null) {
		$this->view->out($this->getTf($id, $args));
	}

	/**
	 * Uses {@see DbtextCollection::tf()} to determine the correct translation and returns it as an {@see UiComponent}.
	 *
	 * @param string $id
	 * @param array $args
	 * @return UiComponent
	 */
	public function getTf(string $id, array $args = null) {
		$translatedText = $this->textService->tf($this->view->getModuleNamespace());
		$replacedText = HtmlBuilderMeta::replace($translatedText, $textHtml, $this->view);
		return new Raw($replacedText);
	}
}