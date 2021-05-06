<?php
namespace nql;

use PHPUnit\Framework\TestCase;
use n2n\persistence\orm\EntityManager;
use n2n\core\N2N;
use n2n\persistence\orm\criteria\Criteria;
use n2n\core\container\N2nContext;
use nql\bo\Member;
use n2n\persistence\meta\structure\Size;
use n2n\persistence\meta\structure\IndexType;
use n2n\persistence\orm\nql\NqlParseException;
use n2n\persistence\meta\data\QueryColumn;
use n2n\persistence\meta\data\QueryConstant;

class NqlTest extends TestCase {

	private $em;
	private $pdo;
	private $metaData;
	private $dataBase;
	private $dialect;
	private $metaEntityFactory;
	
	public function __construct($name = null, array $data = [], $dataName = '') {
		parent::__construct($name, $data, $dataName);
		N2N::getN2nContext()->magicInit($this);
	}
	
	private function _init(EntityManager $em, N2nContext $n2nContext) {
		$this->metaData = $em->getPdo()->getMetaData();
		$this->dataBase = $em->getPdo()->getMetaData()->getDatabase();
		$this->dialect = $this->metaData->getDialect();
		$this->metaEntityFactory = $this->dataBase->createMetaEntityFactory();
		$this->em = $em;
		$this->pdo = $em->getPdo();
		$this->prepareDatabase();
	}
	
	private function prepareDatabase() {
		$stmts = [];
		if (!$this->dataBase->containsMetaEntityName('nql_buddy')) {
			$table = $this->metaEntityFactory->createTable('nql_buddy');
			
			$columnFactory = $table->createColumnFactory();
			$columnFactory->createIntegerColumn('id', Size::INTEGER);
			$table->createIndex(IndexType::PRIMARY, array('id'));
			$columnFactory->createStringColumn('name', 255);
		}
		
		if (!$this->dataBase->containsMetaEntityName('nql_comment')) {
			$table = $this->metaEntityFactory->createTable('nql_comment');
			
			$columnFactory = $table->createColumnFactory();
			$columnFactory->createIntegerColumn('id', Size::INTEGER);
			$table->createIndex(IndexType::PRIMARY, array('id'));
			$columnFactory->createIntegerColumn('blog_article_id', Size::INTEGER);
			$columnFactory->createStringColumn('author', '255');
		}
		
		if ($this->dataBase->containsMetaEntityName('nql_blog_article')) {
			$this->dataBase->removeMetaEntityByName('nql_blog_article');
		}
		
		$table = $this->metaEntityFactory->createTable('nql_blog_article');
		
		$columnFactory = $table->createColumnFactory();
		$id = $columnFactory->createIntegerColumn('id', Size::INTEGER);
		$table->createIndex(IndexType::PRIMARY, array('id'));
		$columnFactory->createStringColumn('title', '255');
		$columnFactory->createIntegerColumn('latest_comment_id', Size::INTEGER);
		$this->dialect->applyIdentifierGeneratorToColumn($this->pdo, $id, '');
		
		$insertBuilder = $this->metaData->createInsertStatementBuilder();
		$insertBuilder->setTable('nql_blog_article');
		$insertBuilder->addColumn(new QueryColumn('title'), new QueryConstant('holeradio'));
		$insertBuilder->toSqlString();
		
		$stmts[] = $this->pdo->prepare($insertBuilder->toSqlString());
		
		if (!$this->dataBase->containsMetaEntityName('nql_article')) {
			$table = $this->metaEntityFactory->createTable('nql_article');
			
			$columnFactory = $table->createColumnFactory();
			$columnFactory->createIntegerColumn('id', Size::INTEGER);
			$table->createIndex(IndexType::PRIMARY, array('id'));
			$columnFactory->createStringColumn('title', '255');
			$columnFactory->createIntegerColumn('active', Size::SHORT);
			$columnFactory->createDateTimeColumn('birthday', Size::SHORT);
		}
		
		if (!$this->dataBase->containsMetaEntityName('nql_assingement_group')) {
			$table = $this->metaEntityFactory->createTable('nql_assingement_group');
			
			$columnFactory = $table->createColumnFactory();
			$columnFactory->createIntegerColumn('id', Size::INTEGER);
			$table->createIndex(IndexType::PRIMARY, array('id'));
		}
		
		if (!$this->dataBase->containsMetaEntityName('nql_member')) {
			$table = $this->metaEntityFactory->createTable('nql_member');
			
			$columnFactory = $table->createColumnFactory();
			$columnFactory->createIntegerColumn('id', Size::INTEGER);
			$table->createIndex(IndexType::PRIMARY, array('id'));
			$columnFactory->createIntegerColumn('assignement_group_id', Size::INTEGER);
		}
		
		if (!$this->dataBase->containsMetaEntityName('nql_enemy')) {
			$table = $this->metaEntityFactory->createTable('nql_enemy');
			
			$columnFactory = $table->createColumnFactory();
			$columnFactory->createIntegerColumn('id', Size::INTEGER);
			$table->createIndex(IndexType::PRIMARY, array('id'));
			$columnFactory->createStringColumn('name', 255);
		}
		
		$this->metaData->getMetaManager()->flush();
		foreach ($stmts as $stmt) {
			$stmt->execute();
		}
	}
	
	public function testOne() {
		$this->assertNql('SELECT a.asdfajsk, asdfasf as a, a.*, COUNT(a.id) AS anz, (SELECT * )
 				FROM Buddy bud RIGHT JOIN Buddy b ON bud.id = b.id OR bud.y = b.y
 						OR ((b.id = :a) AND bud.id = b.y OR bud.last = b.id)', array('a' => 'pf'));
		
		$this->assertNql('SELECT b.name FROM Buddy b WHERE b.id = :id ORDER BY b.id DESC, b.name', array('id' => 2), true);
		$this->assertNql('SELECT c.  author AS id FROM Comment c WHERE c.blogArticle.id = :id ORDER BY c.id ASC HAVING COUNT(id) = :num', array('id' => 1, 'num' => 1), true);
		$this->assertNql('SELECT COUNT(a) FROM nql\bo\Article a WHERE a.title = :title', array('title' => 'holeradio'), true);
// 		$this->assertNql('SELECT o FROM `Order` o 
//  				WHERE o.address.forename = :forename OR EXISTS (SELECT o FROM `Order` o 
//  				WHERE o.address.forename = :forename)', array(':forename' => 'a'));
		$this->assertNql('SELECT cd FROM CourseDate cd WHERE "cd.dateFrom" > :now ORDER BY "cd.dateFrom" ASC', array('now' => new \DateTime()));
		$this->assertNql('SELECT cd FROM CourseDate cd WHERE cd.dateFrom > :now ORDER BY cd.dateFrom ASC', array('now' => new \DateTime()));
		$this->assertNql('SELECT g FROM AssignementGroup g WHERE g.members CONTAINS :m', array(':m' => new Member()), true);
		$this->assertNql('SELECT "sele.ct"."name" FROM Buddy "sele.ct" WHERE "sele.ct"."id" IN (:ids) ORDER BY "sele.ct"."id" DESC, "sele.ct"."name"', array('ids' => array(2, 3)), true);
		$this->assertNql('SELECT b.id FROM Buddy b WHERE b.id = (SELECT COUNT(a) FROM Comment c JOIN c.blogArticle a WHERE c.id = b.id AND b.id = :id AND NOT EXISTS (SELECT e FROM Enemy e WHERE e.id = b.id AND e.name > b.name))', array('id' => 1), true);
		$this->assertNql('Select b FROM Buddy b WHERE NOT EXISTS (SELECT b1 FROM Buddy b1.name = b.name AND b1.id > b.id)', array('id' => 1), true);
		$this->assertNql('SELECT b.id
 				FROM nql\bo\BlogArticle b
 				LEFT JOIN nql\bo\Comment c ON c.blogArticle = b AND c.id = :id
 				WHERE b.title = :title AND NOT EXISTS (SELECT c2 FROM Comment c2 WHERE c.blogArticle = b AND c2.id > b.id)
 				LIMIT 1', array('id' => 1, 'title' => 'test'), true);
// 		$this->assertNql('SELECT u
// 		 				FROM nql\bo\Article u
// 		 				WHERE u.active = :active
// 		 				AND DATE_FORMAT(u.birthday, "%m-%d") = :now',
// 		 				array('active' => true, 'now' => (new \DateTime())->format("m-d")), true);
		$this->assertNql('SELECT t, lp
 				FROM nql\bo\BlogArticle t
 				JOIN FETCH t.latestComment lp
 				LEFT JOIN FETCH t.latestComment q
 				WHERE t.title = :title
 				ORDER BY t.title DESC, lp.id DESC',
 				array('title' => 'sr.id'), true);
		$this->assertNql('SELECT t FROM nql\bo\BlogArticle t WHERE t.id IS NOT NULL', array(), true);
		$this->assertNql('SELECT u FROM nql\bo\Article u WHERE u.id =:id AND u.title = :title', array('id' => 1, 'title' => null), true);
		$this->assertNql('SELECT pt.name, pt.title, pct.seTitle, pct.seDescription, p.online, p.inNavigation
				FROM Comment p
				LEFT JOIN nql\bo\BlogArticle pct ON (p.pageContent = pct.pageContent AND pct.n2nLocale = :n2nLocale),
				Buddy pt
				WHERE p = pt.page AND pt.n2nLocale = :n2nLocale
				ORDER BY p.lft ASC', array('id' => 1, 'title' => 'title'));
		$this->assertNql('SELECT pt.name, pt.title, pct.seTitle, pct.seDescription, p.online, p.inNavigation
				FROM Buddy pt, Comment p
				LEFT JOIN nql\bo\BlogArticle pct ON (p.pageContent = pct.pageContent AND pct.n2nLocale = :n2nLocale)
				WHERE p = pt.page AND pt.n2nLocale = :n2nLocale
				ORDER BY p.lft ASC', array('id' => 1, 'title' => 'title'));
		$this->assertNql('SELECT g
			FROM Group g
			WHERE g.id = :id AND g.organization = :organization', array('id' => 1, 'title' => null));
		$this->assertNql('SELECT e FROM EventT et WHERE et.n2nLocale = :n2nLocale 
                        AND (et.event.dateFrom > :now OR (et.event.dateTo IS NOT NULL AND et.event.dateTo > :now))');
		$this->assertNql('SELECT t FROM nql\bo\BlogArticle t WHERE t.id IS NOT NULL LIMIT :limit', array('limit' => 1), true);
		
		$this->assertNql('SELECT t FROM nql\bo\BlogArticle t WHERE t.id = true', [],  true);
		//N2N::getPdoPool()->getPdo()->getLogger()->clear();
		$this->assertNql('SELECT t FROM nql\bo\BlogArticle t WHERE t.id = 1', [],  true);
		//N2N::getPdoPool()->getPdo()->getLogger()->dump();
		$this->assertNql('SELECT t FROM nql\bo\BlogArticle t WHERE t.id IS NULL', [],  true);
		$this->assertNql('SELECT t FROM nql\bo\BlogArticle t WHERE t.id = true LIMIT 15, 20', [],  true);
		
		try {
			$this->assertNql('SELECT o FROM BlogArticle');
			$this->assertTrue(false);
		} catch (\Throwable $e) {
			$this->assertTrue($e instanceof NqlParseException);
		}
		
		$result = $this->assertNql('SELECT b.id AS "what are you" , b.id AS "hahaha" FROM BlogArticle b ORDER BY b.id DESC , b.id DESC', [], true);
		$this->assertTrue(is_array($result));
		$this->assertTrue(isset(reset($result)['what are you']));
		
		$result = $this->assertNql('SELECT b.id AS "what are you" , b.id AS "hahaha" FROM BlogArticle b WHERE b.title = \'title\' ORDER BY b.id DESC , b.id DESC', [], true);
	}
	
	private function assertNql($nql, array $params = array(), $execute = false) {
		$criteria = $this->em->createNqlCriteria($nql, $params);
		$this->assertTrue($this->em->createNqlCriteria($nql, $params) instanceof Criteria);
		
		if ($execute) {
			$result = $criteria->toQuery()->fetchArray();
			$this->assertTrue(is_array($result));
			return $result;
		}
	}
	
// 	private function runCriteria($nql, array $params = array()) {
// 		$criteria = $this->em->createNqlCriteria($nql, $params);
		
// 		return $criteria->toQuery()->fetchArray();
// 	}
}