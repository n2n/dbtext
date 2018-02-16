<?php
namespace dbtext;

use dbtext\model\DbTextCollection;
use dbtext\model\TextService;
use dbtext\text\Group;
use n2n\impl\web\ui\view\html\HtmlSnippet;
use n2n\web\ui\UiComponent;
use n2n\web\ui\view\View;
use dbtext\text\TextT;

class TextHtmlBuilder {
	/**
	 * @var View $view
	 */
	private $view;

	/**
	 * @var TextService $textService
	 */
	private $textService;

	public function __construct(View $view) {
		$this->view = $view;
		$this->textService = $view->lookup(TextService::class);
	}

	/**
	 * Outputs {@see self::getText()}
	 *
	 * @param string $id
	 * @param array $args
	 */
	public function text(string $id, array $args = null) {
		$this->view->out($this->getText($id, $args));
	}

	/**
	 * getText finds fitting {@see TextT} and returns a {@see UiComponent} with a modified version of {@see TextT::$str}.
	 *
	 * @see DbTextCollection::t()
	 * @param string $id
	 * @param array $args
	 * @return UiComponent
	 */
	public function getText(string $id, array $args = null): UiComponent {
		return new HtmlSnippet($this->textService->t($this->view->getModuleNamespace(), $id, $args, $this->view->getN2nLocale()));
	}

	/**
	 * Outputs {@see self::getTextF()}
	 *
	 * @param string $id
	 * @param array $args
	 */
	public function textF(string $id, array $args = null) {
		$this->view->out($this->getTextF($id, $args));
	}

	/**
	 * getTextF finds fitting {@see TextT} and returns a {@see UiComponent} with a modified version of {@see TextT::$str}.
	 *
	 * @see DbTextCollection::tf()
	 * @param string $id
	 * @param array $args
	 * @return UiComponent
	 */
	public function getTextF(string $id, array $args = null): UiComponent {
		return new HtmlSnippet($this->textService->tf($this->view->getModuleNamespace(), $id, $args, $this->view->getN2nLocale()));
	}
}