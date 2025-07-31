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
	
	public function testSomeArgumentsUnused_UnusedNotShown() {
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
	
	public function testAllArgumentsUnused_UnusedNotShown() {
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
	
	public function testArgumentsProvidedButNotInText_UnusedNotShown() {
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
	
	public function testMixedUsage_UnusedNotShown() {
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
	
	public function testNoTranslationFound_ArgumentsShownViaPrettyKey() {
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
		$this->assertStringContainsString('Fabian', $result);
		$this->assertStringContainsString('ZSC', $result);
	}
} 