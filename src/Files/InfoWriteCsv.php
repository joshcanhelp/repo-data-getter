<?php
declare(strict_types=1);

namespace DxSdk\Data\Files;

use DxSdk\Data\Cleaner;

class InfoWriteCsv extends WriteCsv {

	use Files;

	const ELEMENTS = [
		'Repo' . SEPARATOR . 'description' => 'text',
		'Repo' . SEPARATOR . 'homepage' => 'url',
		'Repo' . SEPARATOR . 'topics' => 'commaSeparateArray',
		'Repo' . SEPARATOR . 'license' . SEPARATOR . 'spdx_id' => 'text',
		'Repo' . SEPARATOR . 'language' => 'text',
		'Repo' . SEPARATOR . 'size' => 'absint',
		'Repo' . SEPARATOR . 'pushed_at' => 'date',
		'Repo' . SEPARATOR . 'created_at' => 'date',
		'Repo' . SEPARATOR . 'private' => 'absint',
		'Repo' . SEPARATOR . 'html_url' => 'url',
		'Community' . SEPARATOR . 'health_percentage' => 'absint',
		'LatestRelease' . SEPARATOR . 'name' => 'text',
		'LatestRelease' . SEPARATOR . 'published_at' => 'date',
		'CI' => 'text',
		//'Coverage',
	];

	private $data = [];

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

		// Make the elements into an assoc array.
		$elements = array_flip( $headers );

		// Set default data to blank values.
		$this->data = array_map( function () { return ''; }, $elements );

		$fileName = $fileName . SEPARATOR . 'info';
		$this->setWriteHandle( sprintf( self::FILEPATH, $fileName ) );
		parent::__construct( $fileName, $headers );
	}

	/**
	 * @param array $addData
	 */
	public function addData( array $addData ) {

		foreach ( self::ELEMENTS as $infoName => $sanitizeFunc ) {
			$infoNameParts = explode( SEPARATOR, $infoName );

			// If the data type is empty, keep as blank.
			if ( empty( $addData[ $infoNameParts[0] ] ) ) {
				continue;
			}

			// No child property to get.
			if ( empty( $infoNameParts[1] ) ) {
				$this->data[$infoName] = Cleaner::$sanitizeFunc( $addData[$infoNameParts[0]] );
				continue;
			}

			if ( empty( $infoNameParts[2] ) ) {
				$this->data[$infoName] = Cleaner::$sanitizeFunc( $addData[$infoNameParts[0]][$infoNameParts[1]] );
				continue;
			}

			$this->data[$infoName] = Cleaner::$sanitizeFunc(
				$addData[$infoNameParts[0]][$infoNameParts[1]][$infoNameParts[2]]
			);
		}
	}

	/**
	 * @return bool
	 *
	 * @throws \Exception
	 */
	public function putClose(): bool {
		$row = array_values( $this->data );
		$this->putRow( $row );
		return $this->close();
	}
}
