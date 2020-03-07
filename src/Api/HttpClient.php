<?php

namespace DxSdk\Data\Api;

use DxSdk\Data\Cleaner;
use DxSdk\Data\Files\ReadJson;
use DxSdk\Data\Files\WriteJson;
use DxSdk\Data\Logger;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\ResponseInterface;

class HttpClient
{

    const DEFAULT_TIMEOUT = 20;

    protected $token;
    protected $baseUrl;
    protected $baseHeaders;

    private $client;
    private $cache = [];

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * HttpClient constructor.
     *
     * @param array $opts - Guzzle options.
     */
    public function __construct( array $opts = [] )
    {
        $this->client = new Client($opts);
    }

    /**
     * @param string $url
     * @param array $headers
     *
     * @return string
     */
    public static function getUrlAsString( string $url, array $headers = [] ): string
    {
        $client = new Client([ 'timeout'  => self::DEFAULT_TIMEOUT ]);
        return $client->send(new Request('GET', $url, $headers))->getBody()->getContents();
    }

    /**
     * @param string $url
     *
     * @return bool
     */
    public static function fileExists( string $url ): bool
    {
        $client = new Client([ 'timeout'  => self::DEFAULT_TIMEOUT ]);
        try {
            $client->send(new Request('GET', $url));
            return true;
        } catch ( \Exception $e ) {
            return false;
        }
    }

    /**
     * Make a GET call.
     *
     * @param string $path    - Relative path from the API base URL.
     * @param array  $headers - Headers to send, if any.
     *
     * @return ResponseInterface
     */
    protected function get( string $path, array $headers = [] ): ResponseInterface
    {
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
     * @param string $path    - Relative path from the API base URL.
     * @param array  $headers - Headers to send, if any.
     *
     * @return string
     */
    protected function getContents( string $path, array $headers = [] ): string
    {
        return $this->get($path, $headers)->getBody()->getContents();
    }

    /**
     * @param string $name
     * @param string $path
     * @param array  $headers
     * @param array  $opts Additional options.
     *
     * @return array
     */
    protected function getSafe( string $name, string $path, array $headers = [], array $opts = [] ): array
    {
        $headers = array_merge($this->baseHeaders, $headers);
        $group = str_replace('/'. $name, '', $path);
        $group = str_replace('/', '-', $group);

        try {
            $responseJson =  $this->getContents($path, $headers);
        } catch ( \Exception $e ) {

            if (404 === $e->getCode() ) {
                if ( $opts['log_404'] ?? false ) {
                    $this->logger->log(sprintf('Failed getting %s: Not Found', $path));
                }
                return [];
            }

            if (403 === $e->getCode() ) {
                $this->logger->log(sprintf('Failed getting %s: Not Authorized', $path));
                return [];
            }

            $this->logger->log(sprintf('Failed getting %s: %s', $path, $e->getMessage()));
            return $this->getPrevious($name, $group);
        }

        $jsonFile = new WriteJson(Cleaner::repoFileName($name), $group);
        $jsonFile->save($responseJson);

        return Cleaner::jsonDecode($responseJson);
    }

    /**
     * @param string $path
     * @param array  $headers
     *
     * @return array
     */
    protected function getPaginated( string $path, array $headers = [] ): array
    {
        $returnDecoded = [];

        do {
            $response = $this->get($path, $headers);
            $responseDecoded = json_decode($response->getBody()->getContents(), true);
            $returnDecoded = array_merge($returnDecoded, $responseDecoded);
            $path = $this->getNextLink($response->getHeaders());
        } while ( $path );

        return $returnDecoded;
    }

    /**
     * @param string $repoName
     * @param string $type
     *
     * @return array
     */
    protected function getPrevious( string $repoName, string $type ): array
    {
        $repoFileName = Cleaner::repoFileName($repoName);

        if (! isset($this->cache[$type][$repoFileName]) ) {
            $previousData = ReadJson::read($type, $repoFileName);
            $this->cache[$type][$repoFileName] = Cleaner::jsonDecode($previousData);
        }

        return $this->cache[$type][$repoFileName] ?? [];
    }

    /**
     * @param array $headers
     *
     * @return null|string
     */
    private function getNextLink( array $headers ) : string
    {
        if (empty($headers['Link'])) {
            return '';
        }

        $link = $headers['Link'][0];
        $link_split = explode(',', $link);

        $link_next_key = false;
        foreach($link_split as $key => $split) {
            if (strpos($split, 'rel="next"') || strpos($split, 'rel=next') ) {
                $link_next_key = $key;
                break;
            }
        }

        if (false === $link_next_key) {
            return '';
        }

        $link_next = explode(';', $link_split[$link_next_key])[0];
        $link_next = trim($link_next, '<> ');
        $link_next = str_replace($this->baseUrl . '/', '', $link_next);

        return $link_next;
    }
}
