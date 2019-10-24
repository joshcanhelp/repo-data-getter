<?php

namespace DxSdk\Data\Api;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

use Psr\Http\Message\ResponseInterface;

class HttpClient {

	const DEFAULT_TIMEOUT = 20;

	protected $token;
	protected $baseUrl;
	protected $baseHeaders;

	private $client;

	/**
	 * HttpClient constructor.
	 *
	 * @param array $opts - Guzzle options.
	 */
	public function __construct( array $opts ) {
		$this->client = new Client( $opts );
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

	/**
	 * @param string $path
	 * @param array $headers
	 *
	 * @return array
	 */
	protected function getPaginated( string $path, array $headers = [] ): array {
		$returnDecoded = [];

		do {
			$response = $this->get( $path, $headers );
			$responseDecoded = json_decode( $response->getBody()->getContents(), true );
			$returnDecoded = array_merge( $returnDecoded, $responseDecoded );
			$path = $this->getNextLink( $response->getHeaders() );
		} while ( $path );

		return $returnDecoded;
	}

	/**
	 * @param array $headers
	 *
	 * @return null|string
	 */
	private function getNextLink( array $headers ) : ?string
	{
		if (empty($headers['Link'])) {
			return null;
		}

		$link = $headers['Link'][0];
		$link_split = explode(',', $link);

		$link_next_key = false;
		foreach($link_split as $key => $split) {
			if ( strpos($split, 'rel="next"') || strpos($split, 'rel=next') ) {
				$link_next_key = $key;
				break;
			}
		}

		if (false === $link_next_key) {
			return null;
		}

		$link_next = explode(';', $link_split[$link_next_key])[0];
		$link_next = trim( $link_next, '<> ' );
		$link_next = str_replace($this->baseUrl . '/', '', $link_next);

		return $link_next;
	}
}
