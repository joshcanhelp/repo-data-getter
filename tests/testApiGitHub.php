<?php

namespace DxSdk\Tests;

use DxSdk\Data\Api\GitHub;
use DxSdk\Data\Files\ReadJson;
use DxSdk\Data\Logger;

class TestApiGitHub extends \PHPUnit\Framework\TestCase {

	const TEST_JSON_FILE = DATA_SAVE_PATH_SLASHED . 'json/org-name--repo-name--DATENOW.json';

	/**
	 * @var Logger
	 */
	public $logger;

	public function setUp() {
		dxsdk_tests_make_json( 'org-name--repo-name' );
		$this->assertTrue( file_exists( self::TEST_JSON_FILE ) );

		$this->logger = new Logger();
	}

	public function tearDown() {
		unlink( self::TEST_JSON_FILE );
		$this->assertFalse( file_exists( self::TEST_JSON_FILE ) );
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testThatRepoApiErrorFallsBackToPreviousJson() {
		define( 'GITHUB_BASE_API', uniqid() );
		$gitHub = new GitHub( uniqid(), $this->logger );

		$response = $gitHub->getRepo( 'org-name/repo-name' );

		$this->assertIsArray( $response );
		$this->assertNotEmpty( $response );

		$readJson = json_decode( ReadJson::read( 'org-name--repo-name' ), true );
		$jsonData = $readJson['Repo'];

		foreach ( $response as $prop => $val ) {
			$this->assertEquals( $jsonData[$prop], $val );
		}
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testThatCommunityApiErrorFallsBackToPreviousJson() {
		define( 'GITHUB_BASE_API', uniqid() );
		$gitHub = new GitHub( uniqid(), $this->logger );

		$response = $gitHub->getCommunity( 'org-name/repo-name' );

		$this->assertIsArray( $response );
		$this->assertNotEmpty( $response );

		$readJson = json_decode( ReadJson::read( 'org-name--repo-name' ), true );
		$jsonData = $readJson['Community'];

		foreach ( $response as $prop => $val ) {
			$this->assertEquals( $jsonData[$prop], $val );
		}
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testThatLatestReleaseApiErrorFallsBackToPreviousJson() {
		define( 'GITHUB_BASE_API', uniqid() );
		$gitHub = new GitHub( uniqid(), $this->logger );

		$response = $gitHub->getLatestRelease( 'org-name/repo-name' );

		$this->assertIsArray( $response );
		$this->assertNotEmpty( $response );

		$readJson = json_decode( ReadJson::read( 'org-name--repo-name' ), true );
		$jsonData = $readJson['LatestRelease'];

		foreach ( $response as $prop => $val ) {
			$this->assertEquals( $jsonData[$prop], $val );
		}
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testThatTrafficViewsApiErrorFallsBackToPreviousJson() {
		define( 'GITHUB_BASE_API', uniqid() );
		$gitHub = new GitHub( uniqid(), $this->logger );

		$response = $gitHub->getTrafficViews( 'org-name/repo-name' );

		$this->assertIsArray( $response );
		$this->assertNotEmpty( $response );

		$readJson = json_decode( ReadJson::read( 'org-name--repo-name' ), true );
		$jsonData = $readJson['TrafficViews'];

		foreach ( $response as $prop => $val ) {
			$this->assertEquals( $jsonData[$prop], $val );
		}
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testThatTrafficClonesApiErrorFallsBackToPreviousJson() {
		define( 'GITHUB_BASE_API', uniqid() );
		$gitHub = new GitHub( uniqid(), $this->logger );

		$response = $gitHub->getTrafficClones( 'org-name/repo-name' );

		$this->assertIsArray( $response );
		$this->assertNotEmpty( $response );

		$readJson = json_decode( ReadJson::read( 'org-name--repo-name' ), true );
		$jsonData = $readJson['TrafficClones'];

		foreach ( $response as $prop => $val ) {
			$this->assertEquals( $jsonData[$prop], $val );
		}
	}
}

