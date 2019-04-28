<?php

namespace DxSdk\Data\Api;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

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
	 * @param $url
	 *
	 * @return string
	 */
	public static function justGet( string $url ): string {
		$client = new Client( [ 'timeout'  => self::DEFAULT_TIMEOUT ] );
		return $client->send( new Request( 'GET', $url ) )->getBody()->getContents();
	}

	/**
	 * @param string $path
	 * @param array $headers
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
}
