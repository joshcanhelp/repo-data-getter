<?php

namespace DxSdk\Data\Api;

use DxSdk\Data\Cleaner;
use DxSdk\Data\Files\ReadJson;
use DxSdk\Data\Logger;
use DxSdk\Data\Files\ReadCsv;
use GuzzleHttp\Exception\ClientException;

final class GitHub extends HttpClient {

	const HEADER_TOPICS = 'application/vnd.github.mercy-preview+json';
	const HEADER_COMMUNITY = 'application/vnd.github.black-panther-preview+json';
	const FAILED_LOG = 'Failed getting %s data for %s: %s';

	/**
	 * @var Logger
	 */
	private $logger;

	/**
	 * @var array
	 */
	private $cache = [];

	public function __construct( $token, Logger $logger ) {
		$this->baseUrl = 'https://api.github.com/repos/';
		$this->baseHeaders = [
			'Accept' => 'application/vnd.github.v3+json',
			'Authorization' => 'token ' . $token,
		];
		$this->logger = $logger;
		parent::__construct();
	}

	/**
	 * Get the basic Repo data.
	 *
	 * @see https://developer.github.com/v3/repos/#get
	 *
	 * @param string $name - Full repo name including org, like "org/repo".
	 *
	 * @return array
	 */
	public function getRepo( string $name ): array {
		$headers = array_merge( $this->baseHeaders, [ 'Accept' => self::HEADER_TOPICS ] );

		try {
			$responseJson =  $this->getContents( $name, $headers );
			return Cleaner::jsonDecode( $responseJson );
		} catch ( \Exception $e ) {
			$this->logger->log( sprintf( 'Failed getting Repo data for %s: %s', $name, $e->getMessage() ) );
			return $this->getPrevious( $name, 'Repo' );
		}
	}

	/**
	 * Get the Community Profile.
	 *
	 * @see https://developer.github.com/v3/repos/community/
	 *
	 * @param string $name - Full repo name including org, like "org/repo".
	 *
	 * @return array
	 */
	public function getCommunity( string $name ): array {
		$path = $name . '/community/profile';
		$headers = array_merge( $this->baseHeaders, [ 'Accept' => self::HEADER_COMMUNITY ] );

		try {
			$responseJson = $this->getContents( $path, $headers );
			return Cleaner::jsonDecode( $responseJson );
		} catch ( \Exception $e ) {
			$this->logger->log( sprintf( 'Failed getting Community data for %s: %s', $name, $e->getMessage() ) );
			return $this->getPrevious( $name, 'Community' );
		}
	}

	/**
	 * Get the latest release.
	 *
	 * @see https://developer.github.com/v3/repos/releases/#get-the-latest-release
	 *
	 * @param string $name - Full repo name including org, like "org/repo".
	 *
	 * @return array
	 */
	public function getLatestRelease( string $name ): array {
		$path = $name . '/releases/latest';

		try {
			$responseJson = $this->getContents( $path, $this->baseHeaders );
			return Cleaner::jsonDecode( $responseJson );
		} catch ( ClientException $e ) {
			// 404 errors for the latest release endpoint means that releases are not used for this repo.
			if ( 404 !== $e->getCode() ) {
				$this->logger->log( sprintf( 'Failed getting LatestRelease data for %s: %s', $name, $e->getMessage() ) );
				return $this->getPrevious( $name, 'LatestRelease' );
			}
			return [];
		}
	}

	/**
	 * @param string $name - Full repo name including org, like "org/repo".
	 *
	 * @return array
	 */
	public function getTrafficRefs( string $name ): array {
		$path = $name . '/traffic/popular/referrers';

		try {
			$responseJson = $this->getContents( $path, $this->baseHeaders );
			return Cleaner::jsonDecode( $responseJson );
		} catch ( \Exception $e ) {
			$this->logger->log( sprintf( 'Failed getting TrafficRefs data for %s: %s', $name, $e->getMessage() ) );
			return $this->getPrevious( $name, 'TrafficRefs' );
		}
	}

	/**
	 * @param string $name - Full repo name including org, like "org/repo".
	 *
	 * @return array
	 */
	public function getTrafficViews( string $name ): array {
		$path = $name . '/traffic/views';

		try {
			$responseJson = $this->getContents( $path, $this->baseHeaders );
			return Cleaner::jsonDecode( $responseJson );
		} catch ( \Exception $e ) {
			$this->logger->log( sprintf( 'Failed getting TrafficViews data for %s: %s', $name, $e->getMessage() ) );
			return $this->getPrevious( $name, 'TrafficViews' );
		}
	}

	/**
	 * @param string $name - Full repo name including org, like "org/repo".
	 *
	 * @return array
	 */
	public function getTrafficClones( string $name ): array {
		$path = $name . '/traffic/clones';

		try {
			$responseJson = $this->getContents( $path, $this->baseHeaders );
			return Cleaner::jsonDecode( $responseJson );
		} catch ( \Exception $e ) {
			$this->logger->log( sprintf( 'Failed getting TrafficClones data for %s: %s', $name, $e->getMessage() ) );
			$readCsv = new ReadCsv( $name );
			return $this->getPrevious( $name, 'TrafficClones' );
		}
	}

	/**
	 * @param string $repoName
	 * @param string $type
	 *
	 * @return array
	 */
	private function getPrevious( string $repoName, string $type ): array {
		$repoFileName = Cleaner::repoFileName( $repoName );

		if ( ! isset( $this->cache[ $repoFileName ] ) ) {
			$previousData = ReadJson::read( $repoFileName );
			$this->cache[ $repoFileName ] = Cleaner::jsonDecode( $previousData );
		}

		return $this->cache[ $repoFileName ][ $type ];
	}
}
