<?php
declare(strict_types=1);

namespace DxSdk\Data\Files;

class InfoWriteCsv extends WriteCsv {

	const ELEMENTS = [
		'Repo' . SEPARATOR . 'description',
		'Repo' . SEPARATOR . 'homepage',
		'Repo' . SEPARATOR . 'topics',
		'Repo' . SEPARATOR . 'license',
		'Repo' . SEPARATOR . 'language',
		'Repo' . SEPARATOR . 'size',
		'Repo' . SEPARATOR . 'pushed_at',
		'Repo' . SEPARATOR . 'created_at',
		'Repo' . SEPARATOR . 'private',
		'Repo' . SEPARATOR . 'html_url',
		'Community' . SEPARATOR . 'health_percentage',
		'Release' . SEPARATOR . 'name',
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

		// Make the elements into an assoc array set to blank values to store the data.
		$elements = array_flip( $headers );
		$this->data = array_map( function () { return ''; }, $elements );

		parent::__construct( $fileName, $headers );
	}

	/**
	 * @param array $addData
	 */
	public function addData( array $addData ) {
		$this->data[$addData[0]] += $addData[1];
	}

	/**
	 * @return bool
	 *
	 * @throws \Exception
	 */
	public function putClose(): bool {
		$row = array_values( $this->data );
		$date = explode( '_', DATE_NOW )[0];
		$time = explode( '_', DATE_NOW )[1];
		$time = str_replace( '-', ':', $time );
		$row = array_merge( [ $date . SEPARATOR . $time, TIME_NOW ], $row );
		$this->putRow( $row );
		return $this->close();
	}
}
