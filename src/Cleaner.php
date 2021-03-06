<?php
declare(strict_types=1);

namespace DxSdk\Data;

class Cleaner
{

    /**
     * @param string|null $text
     *
     * @return string
     */
    public static function text( $text ): string
    {
        return htmlspecialchars((string) $text);
    }

    /**
     * @param string|null $text
     *
     * @return string
     */
    public static function url( $text ): string
    {
        $text = (string) $text;
        return filter_var($text, FILTER_SANITIZE_URL);
    }

    /**
     * @param string $repoName
     *
     * @return string
     */
    public static function orgName( string $repoName ): string
    {
        return explode('/', $repoName)[0];
    }

    /**
     * @param string $datetime
     *
     * @return string
     */
    public static function date( string $datetime ): string
    {
        return self::text(explode('T', $datetime)[0]);
    }

    /**
     * @param $data
     *
     * @return int
     */
    public static function absint( $data ): int
    {
        return abs(intval($data));
    }

    /**
     * @param array $texts
     *
     * @return array
     */
    public static function textArray( array $texts ): array
    {
        return array_map([ self::class, 'text' ], $texts);
    }

    /**
     * @param array $texts
     *
     * @return string
     */
    public static function commaSeparateArray( $texts ): string
    {
        if (empty($texts) || !is_array($texts)) {
            return '';
        }

        return self::text(implode(', ', $texts));
    }

    /**
     * @param null|string $json
     *
     * @return array
     */
    public static function jsonDecode( $json ): array
    {
        return empty($json) ? [] : json_decode($json, true);
    }

    /**
     * @param string $repoName
     *
     * @return string
     */
    public static function repoFileName( string $repoName ): string
    {
        return str_replace('/', SEPARATOR, $repoName);
    }

    /**
     * @param string $repoNamesCsv
     *
     * @return array
     */
    public static function repoNamesArray( string $repoNamesCsv ): array
    {
        $repoNames = explode(PHP_EOL, $repoNamesCsv);
        $repoNames = array_map([ self::class, 'csvFirstCol' ], $repoNames);
        $repoNames = array_map('trim', $repoNames);
        $repoNames = array_filter($repoNames, [ self::class, 'isValidRepoName' ]);
        return array_unique($repoNames);
    }

    /**
     * @param array $repoNames
     *
     * @return array
     */
    public static function orgsFromRepos( array $repoNames ): array
    {
        $orgNames = array_map(
            function ($el) {
                return static::orgName($el);
            }, $repoNames
        );
        return array_unique($orgNames);
    }

    /**
     * @param $el
     *
     * @return string
     */
    public static function csvFirstCol( $el ): string
    {
        return (string) explode(',', $el)[0];
    }

    /**
     * @param $el
     *
     * @return bool
     */
    public static function isValidRepoName( $el ): bool
    {
        return (bool) ! empty($el) && strpos($el, '/');
    }
}
