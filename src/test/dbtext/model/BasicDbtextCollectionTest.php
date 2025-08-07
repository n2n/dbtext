<?php
namespace dbtext\model;

use PHPUnit\Framework\TestCase;
use dbtext\storage\GroupData;
use n2n\l10n\N2nLocale;

class BasicDbtextCollectionTest extends TestCase {
	
	public function testAllArgumentsUsedInText_NoBracketsShown() {
		$data = [
			GroupData::TEXTS_KEY => [
				'my_name_is_firstname_info' => [
					'en' => 'My name is {firstname} and I work at {organisation}'
				]
			],
			GroupData::PLACEHOLDER_JSON_KEY => [
				'my_name_is_firstname_info' => ['firstname' => 'John', 'organisation' => 'Company']
			]
		];
		
		$groupData = new GroupData('test', $data);
		$collection = new BasicDbtextCollection($groupData, new N2nLocale('en'));

		$result = $collection->t('my_name_is_firstname_info', [
			'firstname' => 'Fabian', 
			'organisation' => 'ZSC'
		]);
		$this->assertEquals('My name is Fabian and I work at ZSC', $result);
	}
	
	public function testSomeArgumentsUnused_UnusedNotShownForTranslation() {
		$data = [
			GroupData::TEXTS_KEY => [
				'my_name_is_firstname_info' => [
					'en' => 'My name is {firstname} and I work at {organisation}'
				]
			],
			GroupData::PLACEHOLDER_JSON_KEY => [
				'my_name_is_firstname_info' => ['firstname' => 'John', 'organisation' => 'Company']
			]
		];
		
		$groupData = new GroupData('test', $data);
		$collection = new BasicDbtextCollection($groupData, new N2nLocale('en'));

		$result = $collection->t('my_name_is_firstname_info', [
			'firstname' => 'Fabian',
			'extra_param' => 'unused_value'
		]);

		$this->assertEquals('My name is Fabian and I work at {organisation}', $result);
	}
	
	public function testNoArgumentsProvided_NoBracketsShown() {
		$data = [
			GroupData::TEXTS_KEY => [
				'my_name_is_firstname_info' => [
					'en' => 'My name is {firstname} and I work at {organisation}'
				]
			],
			GroupData::PLACEHOLDER_JSON_KEY => [
				'my_name_is_firstname_info' => ['firstname' => 'John', 'organisation' => 'Company']
			]
		];
		
		$groupData = new GroupData('test', $data);
		$collection = new BasicDbtextCollection($groupData, new N2nLocale('en'));

		$result = $collection->t('my_name_is_firstname_info', []);

		$this->assertEquals('My name is {firstname} and I work at {organisation}', $result);
	}
	
	public function testAllArgumentsUnused_UnusedNotShownForTranslation() {
		$data = [
			GroupData::TEXTS_KEY => [
				'simple_text' => [
					'en' => 'This is a simple text without placeholders'
				]
			],
			GroupData::PLACEHOLDER_JSON_KEY => [
				'simple_text' => []
			]
		];
		
		$groupData = new GroupData('test', $data);
		$collection = new BasicDbtextCollection($groupData, new N2nLocale('en'));

		$result = $collection->t('simple_text', [
			'firstname' => 'Fabian',
			'organisation' => 'ZSC',
			'extra_param' => 'unused'
		]);

		$this->assertEquals('This is a simple text without placeholders', $result);
	}
	
	public function testArgumentsProvidedButNotInText_UnusedNotShownForTranslation() {
		$data = [
			GroupData::TEXTS_KEY => [
				'welcome_message' => [
					'en' => 'Welcome {user_name}!'
				]
			],
			GroupData::PLACEHOLDER_JSON_KEY => [
				'welcome_message' => ['user_name' => 'John']
			]
		];
		
		$groupData = new GroupData('test', $data);
		$collection = new BasicDbtextCollection($groupData, new N2nLocale('en'));

		$result = $collection->t('welcome_message', [
			'firstname' => 'Fabian',
			'organisation' => 'ZSC'
		]);

		$this->assertEquals('Welcome {user_name}!', $result);
	}
	
	public function testMixedUsage_UnusedNotShownForTranslation() {
		$data = [
			GroupData::TEXTS_KEY => [
				'user_info' => [
					'en' => 'User {user_name} works at {company}'
				]
			],
			GroupData::PLACEHOLDER_JSON_KEY => [
				'user_info' => ['user_name' => 'John', 'company' => 'Company']
			]
		];
		
		$groupData = new GroupData('test', $data);
		$collection = new BasicDbtextCollection($groupData, new N2nLocale('en'));

		$result = $collection->t('user_info', [
			'user_name' => 'Fabian',
			'company' => 'ZSC',
			'age' => '30',
			'city' => 'Zurich'
		]);

		$this->assertEquals('User Fabian works at ZSC', $result);
	}
	
	public function testEmptyArgumentsArray_NoBracketsShown() {
		$data = [
			GroupData::TEXTS_KEY => [
				'my_name_is_firstname_info' => [
					'en' => 'My name is {firstname} and I work at {organisation}'
				]
			],
			GroupData::PLACEHOLDER_JSON_KEY => [
				'my_name_is_firstname_info' => ['firstname' => 'John', 'organisation' => 'Company']
			]
		];
		
		$groupData = new GroupData('test', $data);
		$collection = new BasicDbtextCollection($groupData, new N2nLocale('en'));

		$result = $collection->t('my_name_is_firstname_info', []);
		$this->assertEquals('My name is {firstname} and I work at {organisation}', $result);
	}
	
	public function testNoTranslationFound_UnusedArgumentsShown() {
		$data = [
			GroupData::TEXTS_KEY => [],
			GroupData::PLACEHOLDER_JSON_KEY => []
		];
		
		$groupData = new GroupData('test', $data);
		$collection = new BasicDbtextCollection($groupData, new N2nLocale('en'));

		$result = $collection->t('missing_key_txt', [
			'firstname' => 'Fabian',
			'organisation' => 'ZSC'
		]);

		$this->assertStringContainsString('Missing Key', $result);
		$this->assertStringContainsString('[Firstname: Fabian, Organisation: ZSC]', $result);
	}
	
	public function testNoTranslationFound_PartialArgumentsUsed() {
		$data = [
			GroupData::TEXTS_KEY => [],
			GroupData::PLACEHOLDER_JSON_KEY => []
		];
		
		$groupData = new GroupData('test', $data);
		$collection = new BasicDbtextCollection($groupData, new N2nLocale('en'));

		$result = $collection->t('user_info_firstname_company_txt', [
			'firstname' => 'John',
			'company' => 'ZSC',
			'age' => '30',
			'city' => 'Zurich'
		]);

		$this->assertStringContainsString('User Info John ZSC', $result);
		$this->assertStringContainsString('[Age: 30, City: Zurich]', $result);
	}
	
	public function testTranslationWithUnderscorePlaceholders() {
		$data = [
			GroupData::TEXTS_KEY => [
				'user_greeting' => [
					'en' => 'Hello {first_name} {last_name}, welcome to {company_name}!'
				]
			],
			GroupData::PLACEHOLDER_JSON_KEY => [
				'user_greeting' => ['first_name' => 'John', 'last_name' => 'Doe', 'company_name' => 'Company']
			]
		];
		
		$groupData = new GroupData('test', $data);
		$collection = new BasicDbtextCollection($groupData, new N2nLocale('en'));

		$result = $collection->t('user_greeting', [
			'first_name' => 'Jane',
			'last_name' => 'Smith',
			'company_name' => 'TechCorp',
			'age' => '25',
			'city' => 'New York'
		]);

		$this->assertEquals('Hello Jane Smith, welcome to TechCorp!', $result);
	}
	
	public function testNoTranslationFound_UnderscoreArgumentsInKey() {
		$data = [
			GroupData::TEXTS_KEY => [],
			GroupData::PLACEHOLDER_JSON_KEY => []
		];
		
		$groupData = new GroupData('test', $data);
		$collection = new BasicDbtextCollection($groupData, new N2nLocale('en'));

		$result = $collection->t('user_info_first_name_last_name', [
			'first_name' => 'John',
			'last_name' => 'Doe',
			'age' => '30',
			'city' => 'Zurich'
		]);

		$this->assertStringContainsString('User Info John Last', $result);
		$this->assertStringContainsString('[Age: 30, City: Zurich]', $result);
	}
	
	public function testNoTranslationFound_UnusedUnderscoreArgument() {
		$data = [
			GroupData::TEXTS_KEY => [],
			GroupData::PLACEHOLDER_JSON_KEY => []
		];
		
		$groupData = new GroupData('test', $data);
		$collection = new BasicDbtextCollection($groupData, new N2nLocale('en'));

		$result = $collection->t('user_info', [
			'first_name' => 'John',
			'last_name' => 'Doe',
			'age' => '30'
		]);

		$this->assertStringContainsString('User', $result);
		$this->assertStringContainsString('[First Name: John, Last Name: Doe, Age: 30]', $result);
	}
} 