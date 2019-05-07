<?php
declare(strict_types=1);

namespace DxSdk\Data\Files;

use DxSdk\Data\Cleaner;

class InfoWriteCsv extends WriteCsv {

	use Files;

	const ELEMENTS = [
		'Repo' . SEPARATOR . 'name' => 'text',
		'Repo' . SEPARATOR . 'html_url' => 'url',
		'Repo' . SEPARATOR . 'description' => 'text',
		'Repo' . SEPARATOR . 'homepage' => 'url',
		'Repo' . SEPARATOR . 'topics' => 'commaSeparateArray',
		'Repo' . SEPARATOR . 'license' . SEPARATOR . 'spdx_id' => 'text',
		'Repo' . SEPARATOR . 'language' => 'text',
		'Repo' . SEPARATOR . 'private' => 'absint',
		'Repo' . SEPARATOR . 'size' => 'absint',
		'Repo' . SEPARATOR . 'pushed_at' => 'date',
		'Repo' . SEPARATOR . 'created_at' => 'date',
		'Community' . SEPARATOR . 'health_percentage' => 'absint',
		'LatestRelease' . SEPARATOR . 'name' => 'text',
		'LatestRelease' . SEPARATOR . 'published_at' => 'date',
		'CI' => 'text',
		//'Coverage',
	];

	/**
	 * InfoWriteCsv constructor.
	 *
	 * @param string $fileName
	 *
	 * @throws \Exception
	 */
	public function __construct( string $fileName ) {

		// Make sure we don't have any duplicates.
		$headers  = array_keys( self::ELEMENTS );
		$headers  = array_unique( $headers );

		$fileName = 'info' . SEPARATOR . $fileName;
		$this->setWriteHandle( sprintf( self::FILEPATH, $fileName ) );
		parent::__construct( $fileName, $headers );
	}

	/**
	 * @param array $addData
	 */
	public function addData( array $addData ) {

		$rowData = [];
		foreach ( self::ELEMENTS as $infoName => $sanitizeFunc ) {
			$infoNameParts = explode( SEPARATOR, $infoName );

			// If the data type is empty, keep as blank.
			if ( empty( $addData[ $infoNameParts[0] ] ) ) {
				$rowData[] = '';
				continue;
			}

			// No child property to get.
			if ( empty( $infoNameParts[1] ) ) {
				$rowData[] = Cleaner::$sanitizeFunc( $addData[$infoNameParts[0]] );
				continue;
			}

			// No grandchild property to get.
			if ( empty( $infoNameParts[2] ) ) {
				$rowData[] = Cleaner::$sanitizeFunc( $addData[$infoNameParts[0]][$infoNameParts[1]] );
				continue;
			}

			$rowData[] = Cleaner::$sanitizeFunc( $addData[$infoNameParts[0]][$infoNameParts[1]][$infoNameParts[2]] );
		}

		$this->putRow( $rowData );
	}
}
