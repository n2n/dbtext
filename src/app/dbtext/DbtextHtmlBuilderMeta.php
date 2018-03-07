<?php
namespace dbtext;

use n2n\l10n\N2nLocale;

class DbtextHtmlBuilderMeta {
	/**
	 * @var string[] $namespaces
	 */
	private $namespaces;

	/**
	 * @var N2nLocale[] $n2nLocales
	 */
	private $n2nLocales;

	/**
	 * @param string[] $namespaces
	 * @param N2nLocale[] $n2nLocales
	 */
	public function __construct(array $namespaces = array(), array $n2nLocales = array()) {
		$this->namespaces = $namespaces;
		$this->n2nLocales = $n2nLocales;
	}

	/**
	 * @param string $namespace
	 * @param bool $prepend
	 */
	public function assignNamespace(string $namespace, $prepend = false) {
		if ($prepend) {
			$this->namespaces = array($namespace) + $this->namespaces;
		}

		array_push($this->namespaces, $namespace);
	}

	/**
	 * @param string[] ...$namespaces
	 */
	public function assignNamespaces(string ...$namespaces) {
		$this->namespaces = $namespaces;
	}

	/**
	 * @param N2nLocale $n2nLocale
	 * @param bool $prepend
	 */
	public function assignN2nLocale(N2nLocale $n2nLocale, $prepend = false) {
		if ($prepend) {
			$this->n2nLocales = array($n2nLocale) + $this->n2nLocales;
		}

		array_push($this->n2nLocales, $n2nLocale);
	}

	/**
	 * @param N2nLocale[] ...$n2nLocales
	 */
	public function assignN2nLocales(N2nLocale ...$n2nLocales) {
		$this->n2nLocales = $n2nLocales;
	}
}