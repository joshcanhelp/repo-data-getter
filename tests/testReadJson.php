<?php

namespace DxSdk\Tests;

use DxSdk\Data\Files\ReadJson;

class TestReadJson extends \PHPUnit\Framework\TestCase {

	const TEST_JSON_FILE = DATA_SAVE_PATH_SLASHED . 'json/org-name--repo-name--DATENOW.json';

	public function setUp() {
		dxsdk_tests_make_json( 'org-name--repo-name' );
		$this->assertTrue( file_exists( self::TEST_JSON_FILE ) );
	}

	public function tearDown() {
		unlink( self::TEST_JSON_FILE );
		$this->assertFalse( file_exists( self::TEST_JSON_FILE ) );
	}

	public function testThatJsonIsReadProperly() {
		$readJson = ReadJson::read( 'org-name--repo-name' );

		$this->assertNotEmpty( $readJson );

		$readJsonData = json_decode( $readJson, true );

		$this->assertArrayHasKey( 'Repo', $readJsonData );
		$this->assertArrayHasKey( 'Community', $readJsonData );
		$this->assertArrayHasKey( 'LatestRelease', $readJsonData );
		$this->assertArrayHasKey( 'CI', $readJsonData );
		$this->assertArrayHasKey( 'TrafficClones', $readJsonData );
		$this->assertArrayHasKey( 'TrafficViews', $readJsonData );
	}
}
