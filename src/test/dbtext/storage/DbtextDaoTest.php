<?php
namespace dbtext\storage;

use PHPUnit\Framework\TestCase;
use n2n\persistence\orm\EntityManagerFactory;
use n2n\core\container\TransactionManager;
use n2n\test\TestEnv;
use dbtext\test\GeneralTestEnv;

class DbtextDaoTest extends TestCase {
    private DbtextDao $dbtextDao;
    private EntityManagerFactory $emf;
    private TransactionManager $tm;

    protected function setUp(): void {
		GeneralTestEnv::teardown();

        $this->emf = $this->createMock(EntityManagerFactory::class);
        $this->tm = $this->createMock(TransactionManager::class);

		$this->dbtextDao = TestEnv::lookup(DbtextDao::class);
    }

    public function testFormPlaceholdersFromResultWithNullValue() {
        $testData = [['key1', 'en', 'text1', null], ['key2', 'en', 'text2', '{"param1": "value1"}']];

        $method = new \ReflectionMethod(DbtextDao::class, 'formPlaceholdersFromResult');
        $method->setAccessible(true);
		$result = $method->invoke($this->dbtextDao, $testData);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('key1', $result);
        $this->assertArrayHasKey('key2', $result);
        
        $this->assertEquals([], $result['key1']);
        $this->assertEquals(['param1' => 'value1'], json_decode(json_encode($result['key2']), true));
    }

    public function testFormPlaceholdersFromResultWithEmptyArray() {
        $testData = [];
        
        $method = new \ReflectionMethod(DbtextDao::class, 'formPlaceholdersFromResult');
        $method->setAccessible(true);
        
        $result = $method->invoke($this->dbtextDao, $testData);
        
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testFormPlaceholdersFromResultWithInvalidJson() {
        $testData = [['key1', 'en', 'text1', '{invalid json}']];
        
        $method = new \ReflectionMethod(DbtextDao::class, 'formPlaceholdersFromResult');
        $method->setAccessible(true);
        
        $result = $method->invoke($this->dbtextDao, $testData);
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('key1', $result);
        $this->assertEquals([], $result['key1']);
    }
} 