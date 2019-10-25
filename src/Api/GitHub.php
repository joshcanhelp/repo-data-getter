<?php

namespace DxSdk\Data\Api;

use DxSdk\Data\Logger;

final class GitHub extends HttpClient
{

    const API_BASE_URL = 'https://api.github.com/';
    const HEADER_DEFAULT = 'application/vnd.github.v3+json';
    const HEADER_TOPICS = 'application/vnd.github.mercy-preview+json';
    const HEADER_COMMUNITY = 'application/vnd.github.black-panther-preview+json';
    const FAILED_LOG = 'Failed getting %s data for %s: %s';

    protected $logger;
    protected $repoName;

    public function __construct( string $repoName, string $token, Logger $logger, array $opts = [] )
    {

        $this->baseHeaders = [
            'Accept' => self::HEADER_DEFAULT,
            'Authorization' => 'token ' . $token,
        ];

        if (empty($opts) ) {
            $opts = [
                'base_uri' => self::API_BASE_URL,
                'timeout'  => self::DEFAULT_TIMEOUT,
            ];
        }

        $this->repoName = $repoName;
        $this->logger = $logger;
        $this->baseUrl = $opts['base_uri'] ?? null;

        parent::__construct($opts);
    }

    /**
     * @return array
     */
    public function getOrgRepos(): array
    {
        $path = 'orgs/' . $this->repoName . '/repos?type=public&sort=full_name&per_page=100';
        $headers = $this->makeCustomHeaders([ 'Accept' => self::HEADER_TOPICS ]);

        try {
            return $this->getPaginated($path, $headers);
        } catch ( \Exception $e ) {
            $this->logger->log(sprintf('Failed getting Org Repos for "%s": %s', $this->repoName, $e->getMessage()));
            return [];
        }
    }

    /**
     * Get the basic Repo data.
     *
     * @return array
     */
    public function getRepo(): array
    {
        $path = 'repos/' . $this->repoName;
        $headers = $this->makeCustomHeaders([ 'Accept' => self::HEADER_TOPICS ]);
        return $this->getSafe($this->repoName, $path, $headers);
    }

    /**
     * Get the Community Profile.
     *
     * @see https://developer.github.com/v3/repos/community/
     *
     * @return array
     */
    public function getCommunity(): array
    {
        $path = 'repos/' . $this->repoName . '/community/profile';
        $headers = $this->makeCustomHeaders([ 'Accept' => self::HEADER_COMMUNITY ]);
        return $this->getSafe($this->repoName, $path, $headers);
    }

    /**
     * @return array
     */
    public function getLatestRelease(): array
    {
        $path = 'repos/' . $this->repoName . '/releases/latest';
        return $this->getSafe($this->repoName, $path);
    }

    /**
     * @return array
     */
    public function getTrafficViews(): array
    {
        $path = 'repos/' . $this->repoName . '/traffic/views';
        return $this->getSafe($this->repoName, $path);
    }

    /**
     * @return array
     */
    public function getTrafficClones(): array
    {
        $path = 'repos/' . $this->repoName . '/traffic/clones';
        return $this->getSafe($this->repoName, $path);
    }

    /**
     * @return array
     */
    public function getPullRequests(): array
    {
        $path = 'repos/' . $this->repoName . '/pulls';
        $pullsData = $this->getSafe($this->repoName, $path);
        return [
            'count' => count($pullsData),
        ];
    }

    /**
     * @return string
     */
    public function getCiType(): string
    {
        $rootUrl = 'https://github.com/' . $this->repoName . '/tree/master/';
        if ( HttpClient::fileExists( $rootUrl . '.circleci/config.yml' ) ) {
            return 'Circle';
        } elseif ( HttpClient::fileExists( $rootUrl . '.travis.yml' ) ) {
            return 'Travis';
        }

        return '';
    }

    /**
     * @param array $headers
     *
     * @return array
     */
    private function makeCustomHeaders( array $headers ) : array
    {
        return array_merge($this->baseHeaders, $headers);
    }
}
