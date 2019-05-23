<?php

namespace DxSdk\Data\Files;

class ReadJson {

	/**
	 * @param string $repoFileName
	 *
	 * @return bool|string
	 */
	public static function read( string $repoFileName ): string {

		// Read the JSON data directory.
		$jsonDir = DATA_SAVE_PATH_SLASHED . 'json/';
		$allJsonFiles = scandir( $jsonDir, SCANDIR_SORT_DESCENDING );

		// Filter out all files that are not for this repo.
		$repoJsonFiles = array_filter(
			$allJsonFiles,
			function ( $el ) use ( $repoFileName ) {
				return ( 0 === strpos( $el, $repoFileName ) );
			}
		);
		$repoJsonFiles = array_values( $repoJsonFiles );

		return file_get_contents( $jsonDir . $repoJsonFiles[0] );
	}
}
