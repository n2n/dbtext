<?php
namespace rocket\ei\util\factory;

use rocket\ei\manage\entry\EiFieldValidationResult;
use rocket\ei\util\Eiu;
use rocket\impl\ei\component\prop\adapter\entry\EiFieldAdapter;
use n2n\validation\plan\Validator;
use n2n\util\type\TypeConstraint;
use n2n\reflection\magic\MagicMethodInvoker;
use n2n\validation\build\impl\Validate;
use n2n\util\ex\IllegalStateException;
use rocket\ei\manage\entry\EiField;

class EifField {
	/**
	 * @var Eiu
	 */
	private $eiu;
	/**
	 * @var TypeConstraint|null
	 */
	private $typeConstraint;
	/**
	 * @var MagicMethodInvoker
	 */
	private $readerMmi;
	/**
	 * @var MagicMethodInvoker|null
	 */
	private $readMapperMmi;
	/**
	 * @var MagicMethodInvoker|null
	 */
	private $writerMmi;
	/**
	 * @var MagicMethodInvoker|null
	 */
	private $writeMapperMmi;
	/**
	 * @var Validator[]
	 */
	private $validators = [];
	/**
	 * @var \Closure|null
	 */
	private $copierClosure;
	
	/**
	 * @param Eiu $eiu
	 * @param TypeConstraint $typeConstraint
	 * @param \Closure $reader
	 */
	function __construct(Eiu $eiu, ?TypeConstraint $typeConstraint, \Closure $reader) {
		$this->eiu = $eiu;
		$this->typeConstraint = $typeConstraint;
		$this->setReader($reader);
	}
	
	/**
	 * @param \Closure $closure
	 * @return EifField
	 */
	private function setReader(\Closure $closure) {
		if ($closure === null) {
			$this->readerMmi = null;
		}
		
		$this->readerMmi = new MagicMethodInvoker($this->eiu->getN2nContext());
		$this->readerMmi->setClassParamObject(Eiu::class, $this->eiu);
		$this->readerMmi->setReturnTypeConstraint($this->typeConstraint);
		$this->readerMmi->setMethod(new \ReflectionFunction($closure));
		
		return $this;
	}
	
	/**
	 * @param \Closure $closure
	 * @return EifField
	 */
	function setReadMapper(?\Closure $closure) {
		if ($closure === null) {
			$this->readMapperMmi = null;
		}
		
		$this->readMapperMmi = new MagicMethodInvoker($this->eiu->getN2nContext());
		$this->readMapperMmi->setClassParamObject(Eiu::class, $this->eiu);
		$this->readMapperMmi->setReturnTypeConstraint($this->typeConstraint);
		$this->readMapperMmi->setMethod(new \ReflectionFunction($closure));
		
		return $this;
	}
	
	/**
	 * @param \Closure $closure
	 * @return EifField
	 */
	function setWriter(\Closure $closure) {
		if ($closure === null) {
			$this->writerMmi = null;
		}
		
		$this->writerMmi = new MagicMethodInvoker($this->eiu->getN2nContext());
		$this->writerMmi->setClassParamObject(Eiu::class, $this->eiu);
		$this->writerMmi->setMethod(new \ReflectionFunction($closure));
		
		return $this;
	}
	
	/**
	 * @param \Closure $closure
	 * @return EifField
	 */
	function setWriteMapper(?\Closure $closure) {
		if ($closure === null) {
			$this->writeMapperMmi = null; 
		}
		
		$this->writeMapperMmi = new MagicMethodInvoker($this->eiu->getN2nContext());
		$this->writeMapperMmi->setClassParamObject(Eiu::class, $this->eiu);
		$this->writeMapperMmi->setReturnTypeConstraint($this->typeConstraint);
		$this->writeMapperMmi->setMethod(new \ReflectionFunction($closure));
		
		return $this;
	}
	
	function setCopier(?\Closure $closure) {
		$this->copierClosure = $closure;
	}
	
	/**
	 * Alias for {@see EifField::addValidators()}.
	 * @param Validator ...$validators
	 * @return EifField
	 */
	function val(Validator ...$validators) {
		return $this->addValidators(...$validators);
	}
	
	/**
	 * @param Validator ...$validators
	 * @return \rocket\ei\util\factory\EifField
	 */
	function addValidators(Validator ...$validators) {
		array_push($this->validators, ...$validators);
		
		return $this;
	}
	
	/**
	 * @return EiField
	 */
	function toEiField() {
		return new FabricatedEiField($this->eiu, $this->typeConstraint, $this->readerMmi, $this->readMapperMmi, 
				$this->writerMmi, $this->writeMapperMmi, $this->validators, $this->copierClosure);
	}
}

class FabricatedEiField extends EiFieldAdapter {
	/**
	 * @var Eiu
	 */
	private $eiu;
	/**
	 * @var TypeConstraint|null
	 */
	private $typeConstraint;
	/**
	 * @var MagicMethodInvoker
	 */
	private $readerMmi;
	/**
	 * @var MagicMethodInvoker|null
	 */
	private $readMapperMmi;
	/**
	 * @var MagicMethodInvoker|null
	 */
	private $writerMmi;
	/**
	 * @var MagicMethodInvoker|null
	 */
	private $writeMapperMmi;
	/**
	 * @var Validator[]
	 */
	private $validators = [];
	/**
	 * @var \Closure|null
	 */
	private $copierClosure;
	
	/**
	 * @param Eiu $eiu
	 * @param TypeConstraint $typeConstraint
	 * @param MagicMethodInvoker $readerMmi
	 * @param MagicMethodInvoker $readMapperMmi
	 * @param MagicMethodInvoker $writerMmi
	 * @param MagicMethodInvoker $writeMapperMmi
	 * @param array $validators
	 * @param \Closure $copierClosure
	 */
	function __construct(Eiu $eiu, ?TypeConstraint $typeConstraint, MagicMethodInvoker $readerMmi, 
			?MagicMethodInvoker $readMapperMmi, ?MagicMethodInvoker $writerMmi, 
			?MagicMethodInvoker $writeMapperMmi, array $validators, ?\Closure $copierClosure) {
		$this->eiu = $eiu;
		$this->typeConstraint = $typeConstraint;
		$this->readerMmi = $readerMmi;
		$this->readMapperMmi = $readMapperMmi;
		$this->writerMmi = $writerMmi;
		$this->writeMapperMmi = $writeMapperMmi;
		$this->validators = $validators;
		$this->copierClosure = $copierClosure;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\impl\ei\component\prop\adapter\entry\EiFieldAdapter::checkValue()
	 */
	protected function checkValue($value) {
		if ($this->typeConstraint !== null) {
			$this->typeConstraint->validate($value);
		}
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\impl\ei\component\prop\adapter\entry\EiFieldAdapter::readValue()
	 */
	protected function readValue() {
		$value = $this->readerMmi->invoke();
		if ($this->readerMmi !== null) {
			$value = $this->readerMmi->invoke(null, null, [$value]);
		}
		return $value;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\entry\EiField::isWritable()
	 */
	public function isWritable(): bool {
		return $this->writerMmi !== null;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\impl\ei\component\prop\adapter\entry\EiFieldAdapter::writeValue()
	 */
	protected function writeValue($value) {
		if ($this->writeMapperMmi !== null) {
			$value = $this->writeMapperMmi->invoke(null, null, [$value]);
		}
		
		IllegalStateException::assertTrue($this->writerMmi !== null);
		return $this->writerMmi->invoke(null, null, [$value]);
	}
	
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\impl\ei\component\prop\adapter\entry\EiFieldAdapter::isValueValid()
	 */
	protected function isValueValid($value) {
		if (empty($this->validators)) {
			return true;
		}
		
		return Validate::value($value)->val(...$this->validators)
				->test($this->eiu->getN2nContext());
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\impl\ei\component\prop\adapter\entry\EiFieldAdapter::validateValue()
	 */
	protected function validateValue($value, EiFieldValidationResult $validationResult) {
		if (empty($this->validators)) {
			return;
		}
		
		$valueValidationResult = Validate::value($value)->val(...$this->validators)->exec($this->eiu->getN2nContext());
		if (!$valueValidationResult->hasErrors()) {
			return;
		}
		
		foreach ($valueValidationResult->getErrorMap()->getAllMessages() as $message) {
			$validationResult->addError($message);
		}
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\entry\EiField::isCopyable()
	 */
	public function isCopyable(): bool {
		return $this->copierClosure !== null;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \rocket\ei\manage\entry\EiField::copyValue()
	 */
	public function copyValue(Eiu $copyEiu) {
		IllegalStateException::assertTrue($this->copierClosure !== null);
		
		$copierMmi = new MagicMethodInvoker($this->eiu->getN2nContext());
		$copierMmi->setParamValue('origEiu', $this->eiu);
		$copierMmi->setParamValue('copyEiu', $copyEiu);
		$copierMmi->setReturnTypeConstraint($this->typeConstraint);
		return $copierMmi->invoke(null, $this->copierClosure, [$this->getValue()]);
	}	
}