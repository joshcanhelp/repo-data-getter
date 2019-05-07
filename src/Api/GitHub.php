<?php

namespace DxSdk\Data\Api;

use DxSdk\Data\Cleaner;
use \GuzzleHttp\Exception\ClientException;

final class GitHub extends HttpClient {

	const HEADER_TOPICS = 'application/vnd.github.mercy-preview+json';
	const HEADER_COMMUNITY = 'application/vnd.github.black-panther-preview+json';

	public function __construct( $token ) {
		$this->baseUrl = 'https://api.github.com/repos/';
		$this->baseHeaders = [
			'Accept' => 'application/vnd.github.v3+json',
			'Authorization' => 'token ' . $token,
		];
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
		$bodyJson = $this->getContents( $name, $headers );
		return Cleaner::jsonDecode( $bodyJson );
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
		$bodyJson = $this->getContents( $path, $headers );
		return Cleaner::jsonDecode( $bodyJson );
	}

	/**
	 * Get the latest release.
	 *
	 * @see https://developer.github.com/v3/repos/releases/#get-the-latest-release
	 *
	 * @param string $name - Full repo name including org, like "org/repo".
	 *
	 * @return string
	 */
	public function getLatestRelease( string $name ): string {
		$path = $name . '/releases/latest';
		try {
			$response = $this->get( $path, $this->baseHeaders );
		} catch ( ClientException $e ) {
			// 404 errors for the latest release endpoint means that releases are not used for this repo.
			if ( 404 === $e->getCode() ) {
				return '{}';
			}
			throw $e;
		}
		return $response->getBody()->getContents();
	}

	/**
	 * @param string $name - Full repo name including org, like "org/repo".
	 *
	 * @return array
	 */
	public function getTrafficRefs( string $name ): array {
		$path = $name . '/traffic/popular/referrers';
		$bodyJson = $this->getContents( $path, $this->baseHeaders );
		return Cleaner::jsonDecode( $bodyJson );
	}

	/**
	 * @param string $name - Full repo name including org, like "org/repo".
	 *
	 * @return array
	 */
	public function getTrafficPaths( string $name ): array {
		$path = $name . '/traffic/popular/paths';
		$bodyJson = $this->getContents( $path, $this->baseHeaders );
		return Cleaner::jsonDecode( $bodyJson );
	}

	/**
	 * @param string $name - Full repo name including org, like "org/repo".
	 *
	 * @return array
	 */
	public function getTrafficViews( string $name ): array {
		$path = $name . '/traffic/views';
		$bodyJson = $this->getContents( $path, $this->baseHeaders );
		return Cleaner::jsonDecode( $bodyJson );
	}

	/**
	 * @param string $name - Full repo name including org, like "org/repo".
	 *
	 * @return array
	 */
	public function getTrafficClones( string $name ): array {
		$path = $name . '/traffic/clones';
		$bodyJson = $this->getContents( $path, $this->baseHeaders );
		return Cleaner::jsonDecode( $bodyJson );
	}
}
