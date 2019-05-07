<?php
declare(strict_types=1);

namespace DxSdk\Data\Files;

class InfoWriteCsv extends WriteCsv {

	const ELEMENTS = [
		'Repo' . SEPARATOR . 'description',
		'Repo' . SEPARATOR . 'homepage',
		'Repo' . SEPARATOR . 'topics',
		'Repo' . SEPARATOR . 'license' . SEPARATOR . 'spdx_id',
		'Repo' . SEPARATOR . 'language',
		'Repo' . SEPARATOR . 'size',
		'Repo' . SEPARATOR . 'pushed_at',
		'Repo' . SEPARATOR . 'created_at',
		'Repo' . SEPARATOR . 'private',
		'Repo' . SEPARATOR . 'html_url',
		'Community' . SEPARATOR . 'health_percentage',
		'LatestRelease' . SEPARATOR . 'name',
		'LatestRelease' . SEPARATOR . 'published_at',
		'Coverage',
		'CI',
	];

	private $data = [];

	/**
	 * StatsCsv constructor.
	 *
	 * @param string $fileName
	 *
	 * @throws \Exception
	 */
	public function __construct( string $fileName ) {

		// Make sure we don't have any duplicates.
		$headers  = array_unique( self::ELEMENTS );

		// Make the elements into an assoc array.
		$elements = array_flip( $headers );

		// Set default data to blank values.
		$this->data = array_map( function () { return ''; }, $elements );

		parent::__construct( $fileName . SEPARATOR . 'info', $headers );
	}

	/**
	 * @param array $addData
	 */
	public function addData( array $addData ) {
		if ( isset( $this->data[$addData[0]] ) ) {
			$this->data[$addData[0]] = $addData[1];
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
