<?php

namespace DxSdk\Tests;

use DxSdk\Data\Files\ReadStatsCsv;

class TestReadStatsCsv extends \PHPUnit\Framework\TestCase {

	const TEST_CSV_FILE = DATA_SAVE_PATH_SLASHED . 'csv/org-name--repo-name-stats.csv';
	const TEST_EMPTY_CSV_FILE = DATA_SAVE_PATH_SLASHED . 'csv/org-name--repo-name-empty.csv';

	public function setUp() {
		dxsdk_tests_make_stats_csv( self::TEST_CSV_FILE );
		dxsdk_tests_make_stats_csv( self::TEST_EMPTY_CSV_FILE, false );
		$this->assertTrue( file_exists( self::TEST_CSV_FILE ) );
		$this->assertTrue( file_exists( self::TEST_EMPTY_CSV_FILE ) );
	}

	public function tearDown() {
		unlink( self::TEST_CSV_FILE );
		unlink( self::TEST_EMPTY_CSV_FILE );
		$this->assertFalse( file_exists( self::TEST_CSV_FILE ) );
		$this->assertFalse( file_exists( self::TEST_EMPTY_CSV_FILE ) );
	}

	public function testThatLastStatsAreRetrieved() {
		$readStatsCsv = new ReadStatsCsv( 'org-name/repo-name-stats' );
		$csvArray = $readStatsCsv->getCsv();
		$statsArray = $readStatsCsv->getPreviousStats( 'Repo' );

		$this->assertIsArray( $statsArray );
		$this->assertNotEmpty( $statsArray );

		$headers = array_flip( $csvArray[0] );
		foreach ( $statsArray as $name => $stat ) {
			$csvIndex = $headers['Repo--' . $name];
			$this->assertEquals( $csvArray[2][$csvIndex], $stat );
		}
	}

	public function testThatEmptyCsvReturnsZeros() {
		$readStatsCsv = new ReadStatsCsv( 'org-name/repo-name-empty' );
		$statsArray = $readStatsCsv->getPreviousStats( 'Repo' );

		$this->assertIsArray( $statsArray );
		$this->assertNotEmpty( $statsArray );

		foreach ( $statsArray as $stat ) {
			$this->assertEquals( 0, $stat );
		}
	}
}
