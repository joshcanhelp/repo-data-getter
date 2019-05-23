<?php

namespace DxSdk\Tests;

use DxSdk\Data\Files\ReadCsv;

class TestReadCsv extends \PHPUnit\Framework\TestCase {

	const TEST_CSV_FILE = DATA_SAVE_PATH_SLASHED . 'csv/org-name--repo-name.csv';

	public function setUp() {
		dxsdk_tests_make_stats_csv( self::TEST_CSV_FILE );
		$this->assertTrue( file_exists( self::TEST_CSV_FILE ) );
	}

	public function tearDown() {
		unlink( self::TEST_CSV_FILE );
		$this->assertFalse( file_exists( self::TEST_CSV_FILE ) );
	}

	public function testThatRepoNameIsConvertedToFileName() {
		$readCsv = new ReadCsv( 'org-name/repo-name' );
		$this->assertEquals( 'org-name--repo-name', $readCsv->getFileName() );

		$readCsv = new ReadCsv( 'org-name--repo-name' );
		$this->assertEquals( 'org-name--repo-name', $readCsv->getFileName() );
	}

	public function testThatCsvIsReadAsArray() {
		$readCsv = new ReadCsv( 'org-name/repo-name' );
		$csvArray = $readCsv->getCsv();

		$this->assertCount( 3, $csvArray );
		$this->assertCount( 10, $csvArray[0] );
		$this->assertCount( 10, $csvArray[1] );
		$this->assertCount( 10, $csvArray[2] );
	}
}
