<?php

namespace DxSdk\Data\Api;

final class GitHub extends HttpClient {

	private const HEADER_TOPICS = 'application/vnd.github.mercy-preview+json';
	private const HEADER_COMMUNITY = 'application/vnd.github.black-panther-preview+json';

	public function __construct( $token ) {
		$this->baseUrl = 'https://api.github.com/repos/';
		$this->baseHeaders = [
			'Accept' => 'application/vnd.github.v3+json',
			'Authorization' => 'token ' . $token,
		];
		parent::__construct();
	}

	/**
	 * @param string $name - Full repo name including org, like "org/repo".
	 *
	 * @return string
	 */
	public function getRepo( string $name ): string {
		$headers = array_merge( $this->baseHeaders, [ 'Accept' => self::HEADER_TOPICS ] );
		return $this->get( $name, $headers )->getBody()->getContents();
	}

	/**
	 * Get the Community Profile.
	 *
	 * @see https://developer.github.com/v3/repos/community/
	 *
	 * @param string $name - Full repo name including org, like "org/repo".
	 *
	 * @return string
	 */
	public function getCommunity( string $name ): string {
		$path = $name . '/community/profile';
		$headers = array_merge( $this->baseHeaders, [ 'Accept' => self::HEADER_COMMUNITY ] );
		return $this->get( $path, $headers )->getBody()->getContents();
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
		return $this->get( $path, $this->baseHeaders )->getBody()->getContents();
	}

	/**
	 * @param string $name - Full repo name including org, like "org/repo".
	 *
	 * @return string
	 */
	public function getTrafficRefs( string $name ): string {
		$path = $name . '/traffic/popular/referrers';
		return $this->get( $path, $this->baseHeaders )->getBody()->getContents();
	}

	/**
	 * @param string $name - Full repo name including org, like "org/repo".
	 *
	 * @return string
	 */
	public function getTrafficPaths( string $name ): string {
		$path = $name . '/traffic/popular/paths';
		return $this->get( $path, $this->baseHeaders )->getBody()->getContents();
	}

	/**
	 * @param string $name - Full repo name including org, like "org/repo".
	 *
	 * @return string
	 */
	public function getTrafficViews( string $name ): string {
		$path = $name . '/traffic/views';
		return $this->get( $path, $this->baseHeaders )->getBody()->getContents();
	}

	/**
	 * @param string $name - Full repo name including org, like "org/repo".
	 *
	 * @return string
	 */
	public function getTrafficClones( string $name ): string {
		$path = $name . '/traffic/clones';
		return $this->get( $path, $this->baseHeaders )->getBody()->getContents();
	}
}
