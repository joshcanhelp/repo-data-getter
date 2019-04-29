<?php
declare(strict_types=1);

namespace DxSdk\Data;

class Cleaner {

	/**
	 * @param string $text
	 *
	 * @return string
	 */
	public static function text( string $text ): string {
		return htmlspecialchars( $text );
	}

	/**
	 * @param string $text
	 *
	 * @return string
	 */
	public static function url( string $text ): string  {
		return (string) filter_var( $text, FILTER_SANITIZE_URL );
	}

	/**
	 * @param string $repoName
	 *
	 * @return string
	 */
	public static function orgName( string $repoName ): string  {
		return explode( '/', $repoName )[0];
	}

	/**
	 * @param string $datetime
	 *
	 * @return string
	 */
	public static function date( string $datetime ): string  {
		return self::text( explode( 'T', $datetime )[0] );
	}

	/**
	 * @param $text
	 *
	 * @return int
	 */
	public static function absint( $text ): int {
		return abs( intval( $text ) );
	}

	/**
	 * @param array $texts
	 *
	 * @return array
	 */
	public static function textArray( array $texts ): array {
		return array_map( [ self::class, 'text' ], $texts );
	}

	/**
	 * @param array $repoNames
	 *
	 * @return array
	 */
	public static function repoNamesArray( array $repoNames ): array {
		$repoNames = array_map( '\DxSdk\Data\Cleaner::csvFirstCol', $repoNames );
		$repoNames = array_filter( $repoNames, '\DxSdk\Data\Cleaner::isValidRepoName' );
		return $repoNames;
	}

	/**
	 * @param $el
	 *
	 * @return string
	 */
	public static function csvFirstCol( $el ): string {
		return (string) explode( ',', $el )[0];
	}

	/**
	 * @param $el
	 *
	 * @return bool
	 */
	public static function isValidRepoName( $el ): bool {
		return (bool) ! empty( $el ) && strpos( $el, '/' );
	}
}
