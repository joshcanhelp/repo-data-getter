<?php

namespace DxSdk\Data\Api;

use DxSdk\Data\Cleaner;
use DxSdk\Data\Files\ReadJson;
use DxSdk\Data\Logger;

final class CodeCov extends HttpClient
{

    protected $logger;
    protected $repoName;

    public function __construct( string $repoName, string $token, Logger $logger, array $opts = [] )
    {

        $this->baseHeaders = [
            'Accept' => 'application/json',
            'Authorization' => 'token ' . $token,
        ];

        if (empty($opts) ) {
            $opts = [
                'base_uri' => 'https://codecov.io/api/',
                'timeout'  => self::DEFAULT_TIMEOUT,
            ];
        }

        $this->repoName = $repoName;
        $this->logger = $logger;
        $this->baseUrl = $opts['base_uri'] ?? null;

        parent::__construct($opts);
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
    public function getCoverage(): array
    {
        $response = $this->getSafe($this->repoName, 'gh/' . $this->repoName);
        if (isset($response['commit']) && isset($response['commit']['totals']) ) {
            return $response['commit']['totals'];
        }
        return [ 'c' => 0 ];
    }
}
