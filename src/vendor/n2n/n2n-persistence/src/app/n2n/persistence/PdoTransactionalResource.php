<?php
/*
 * Copyright (c) 2012-2016, Hofmänner New Media.
 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
 *
 * This file is part of the N2N FRAMEWORK.
 *
 * The N2N FRAMEWORK is free software: you can redistribute it and/or modify it under the terms of
 * the GNU Lesser General Public License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * N2N is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
 * the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details: http://www.gnu.org/licenses/
 *
 * The following people participated in this project:
 *
 * Andreas von Burg.....: Architect, Lead Developer
 * Bert Hofmänner.......: Idea, Frontend UI, Community Leader, Marketing
 * Thomas Günther.......: Developer, Hangar
 */
namespace n2n\persistence;

use n2n\core\container\TransactionalResource;
use n2n\core\container\Transaction;

class PdoTransactionalResource implements TransactionalResource {
	private $beginClosure;
	private $prepareCommitClosure;
	private $commitClosure;
	private $rollBackClosure;

	public function __construct(\Closure $beginClosure, \Closure $prepareCommitClosure, 
			\Closure $commitClosure, \Closure $rollBackClosure) {
		$this->beginClosure = new \ReflectionFunction($beginClosure);
		$this->prepareCommitClosure = new \ReflectionFunction($prepareCommitClosure);
		$this->commitClosure = new \ReflectionFunction($commitClosure);
		$this->rollBackClosure = new \ReflectionFunction($rollBackClosure);	
	}
	/* (non-PHPdoc)
	 * @see \n2n\core\container\TransactionalResource::beginTransaction()
	 */
	public function beginTransaction(Transaction $transaction) {
		$this->beginClosure->invoke($transaction);
	}
	/* (non-PHPdoc)
	 * @see \n2n\core\container\TransactionalResource::prepareCommit()
	 */
	public function prepareCommit(Transaction $transaction): bool {
		return $this->prepareCommitClosure->invoke($transaction);
	}
	/* (non-PHPdoc)
	 * @see \n2n\core\container\TransactionalResource::commit()
	 */
	public function commit(Transaction $transaction) {
		$this->commitClosure->invoke($transaction);
	}
	/* (non-PHPdoc)
	 * @see \n2n\core\container\TransactionalResource::rollBack()
	 */
	public function rollBack(Transaction $transaction) {
		$this->rollBackClosure->invoke($transaction);
	}
}
