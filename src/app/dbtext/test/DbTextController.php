<?php

namespace dbtext\test;

use dbtext\storage\DbTextDao;
use dbtext\storage\TextDao;
use dbtext\text\Group;
use dbtext\text\TextT;
use n2n\core\container\AppCache;
use n2n\l10n\N2nLocale;
use n2n\persistence\orm\EntityManager;

class DbTextController extends \n2n\web\http\controller\ControllerAdapter {
	public function index() {
		$this->forward('..\test\test.html');
	}
}