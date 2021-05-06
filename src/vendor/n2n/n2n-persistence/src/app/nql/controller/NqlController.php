<?php
namespace nql\controller;

use n2n\web\http\controller\ControllerAdapter;
use n2n\persistence\orm\EntityManager;
use nql\bo\Buddy;

class NqlController extends ControllerAdapter {
	
	public function index(EntityManager $em) {
		test($em->createNqlCriteria('SELECT b.name AS name FROM Buddy b ORDER BY b.name')->toQuery()->fetchArray());
		
		test($em->createCriteria()->select('b.name', 'name')->select('b.name', 'hahahah')->from(Buddy::getClass(), 'b')->toQuery()->fetchArray());
	}
}