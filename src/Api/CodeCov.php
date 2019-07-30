<?php

namespace DxSdk\Data\Api;

use DxSdk\Data\Cleaner;
use DxSdk\Data\Files\ReadJson;
use DxSdk\Data\Logger;

final class CodeCov extends HttpClient {

	/**
	 * @var Logger
	 */
	private $logger;

	/**
	 * @var array
	 */
	private $cache = [];

	public function __construct( string $token, Logger $logger, array $opts = [] ) {

		$this->baseHeaders = [
			'Accept' => 'application/json',
			'Authorization' => 'token ' . $token,
		];

		$this->logger = $logger;

		if ( empty( $opts ) ) {
			$opts = [
				'base_uri' => 'https://codecov.io/api/gh/',
				'timeout'  => self::DEFAULT_TIMEOUT,
			];
		}

		parent::__construct( $opts );
	}

	/**
	 * Get the repo's code coverage.
	 *
	 * @see https://docs.codecov.io/reference#totals
	 *
	 * @param string $name - Full repo name including org, like "org/repo".
	 *
	 * @return array
	 */
	public function getCoverage( string $name ): array {
		try {
			$responseJson =  $this->getContents( $name, $this->baseHeaders );
			$responseJson = Cleaner::jsonDecode( $responseJson );
			if ( isset( $responseJson['commit'] ) && isset( $responseJson['commit']['totals'] ) ) {
				return $responseJson['commit']['totals'];
			}
			return [ 'c' => 0 ];
		} catch ( \Exception $e ) {
			$this->logger->log( sprintf( 'Failed getting CodeCov data for %s: %s', $name, $e->getMessage() ) );
			return $this->getPrevious( $name, 'CodeCov' );
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
