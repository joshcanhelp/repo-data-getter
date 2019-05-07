<?php

namespace DxSdk\Data\Api;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

use Psr\Http\Message\ResponseInterface;

use League\Uri\Exception;

class HttpClient {

	const DEFAULT_TIMEOUT = 10;

	protected $token;
	protected $baseUrl;
	protected $baseHeaders;

	private $client;

	public function __construct() {

		if ( ! $this->baseUrl ) {
			throw new Exception( 'No API base URL!' );
		}

		$this->client = new Client( [
			'base_uri' => $this->baseUrl,
			'timeout'  => self::DEFAULT_TIMEOUT,
		] );
	}

	/**
	 * @param string $url
	 *
	 * @return string
	 */
	public static function getUrlAsString( string $url ): string {
		$client = new Client( [ 'timeout'  => self::DEFAULT_TIMEOUT ] );
		return $client->send( new Request( 'GET', $url ) )->getBody()->getContents();
	}

	/**
	 * @param string $url
	 *
	 * @return bool
	 */
	public static function fileExists( string $url ): bool {
		$client = new Client( [ 'timeout'  => self::DEFAULT_TIMEOUT ] );
		try {
			$client->send( new Request( 'GET', $url ) );
			return true;
		} catch ( \Exception $e ) {
			return false;
		}
	}

	/**
	 * Make a GET call.
	 *
	 * @param string $path - Relative path from the API base URL.
	 * @param array $headers - Headers to send, if any.
	 *
	 * @return ResponseInterface
	 */
	protected function get( string $path, array $headers = [] ): ResponseInterface {
		return $this->client->send(
			new Request('GET', $path),
			[
				'headers' => $headers
			]
		);
	}

	/**
	 * Get the body contents as a string.
	 *
	 * @param string $path - Relative path from the API base URL.
	 * @param array $headers - Headers to send, if any.
	 *
	 * @return string
	 */
	protected function getContents( string $path, array $headers = [] ): string {
		return $this->get( $path, $headers )->getBody()->getContents();
	}
}
