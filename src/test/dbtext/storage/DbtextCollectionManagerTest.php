<?php
namespace dbtext\storage;

use PHPUnit\Framework\TestCase;
use n2n\test\TestEnv;
use n2n\core\container\N2nContext;
use n2n\context\LookupManager;
use dbtext\text\Text;
use dbtext\text\Group;
use dbtext\test\GeneralTestEnv;

class DbtextCollectionManagerTest extends TestCase {

	/**
	 * @var DbtextCollectionManager
	 */
	private $dbTextCollectionManager;

	/**
	 * @var GroupData
	 */
	private $groupData;

	private $testKey = 'testKey';

	function setUp(): void {
		GeneralTestEnv::teardown();

		$this->dbTextCollectionManager = TestEnv::lookup(DbtextCollectionManager::class);
		$this->groupData = new GroupData('test',[GroupData::TEXTS_KEY => ['first_translation_txt' => 'first'],
				GroupData::PLACEHOLDER_JSON_KEY => ['first_translation_txt' => '["placeholder" => "placeholder"]']]);
	}

	function testKeyAdded() {
		$tx = TestEnv::createTransaction(true);
		$this->dbTextCollectionManager->keyAdded($this->testKey, $this->groupData, ['placeholder']);
		$tx->commit();

		$tx = TestEnv::createTransaction(true);
		$this->assertNotNull(TestEnv::tem()->createSimpleCriteria(Text::getClass(),
				['key' => $this->testKey])->toQuery()->fetchSingle());
		$tx->commit();
	}

	function testKeyAddedWithoutTransaction() {
		$this->dbTextCollectionManager->keyAdded($this->testKey, $this->groupData, ['placeholder']);

		$tx = TestEnv::createTransaction(true);
		$this->assertNotNull(TestEnv::tem()->createSimpleCriteria(Text::getClass(),
				['key' => $this->testKey])->toQuery()->fetchSingle());
		$tx->commit();
	}

	function testPlaceholdersChanged() {
		$changedPlaceholders = ['changedPlaceholders'];
		$ns = 'test';

		$tx = TestEnv::createTransaction();
		$group = new Group($ns);
		$text = new Text($this->testKey, $group, ['placeholder']);
		TestEnv::tem()->persist($group);
		TestEnv::tem()->persist($text);
		$this->dbTextCollectionManager->placeholdersChanged($this->testKey, $ns, $changedPlaceholders);
		$tx->commit();

		$tx = TestEnv::createTransaction(true);
		$text = TestEnv::tem()->createSimpleCriteria(Text::getClass(), ['key' => $this->testKey])->toQuery()->fetchSingle();
		$this->assertEquals($changedPlaceholders, $text->getPlaceholders());
		$tx->commit();
	}

	function testPlaceholdersChangedWithoutTransaction() {
		$changedPlaceholders = ['changedPlaceholders'];
		$ns = 'test';

		$tx = TestEnv::createTransaction();
		$group = new Group($ns);
		$text = new Text($this->testKey, $group, ['placeholder']);
		TestEnv::tem()->persist($group);
		TestEnv::tem()->persist($text);
		$tx->commit();

		$this->dbTextCollectionManager->placeholdersChanged($this->testKey, $ns, $changedPlaceholders);

		TestEnv::createTransaction(true);
		$text = TestEnv::tem()->createSimpleCriteria(Text::getClass(), ['key' => $this->testKey])->toQuery()->fetchSingle();
		$this->assertEquals($changedPlaceholders, $text->getPlaceholders());
	}
}