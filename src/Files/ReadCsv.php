<?php
declare(strict_types=1);

namespace DxSdk\Data\Files;

class ReadCsv {

	use Files;

	/**
	 * Csv constructor.
	 *
	 * @param string $fileName
	 */
	public function __construct( string $fileName ) {
		$this->setReadHandle( sprintf( WriteCsv::FILEPATH, $fileName ) );
	}

	/**
	 * @return array
	 */
	public function getCsv(): array {
		$csvArray = [];
		while ( $csvLine = fgetcsv( $this->handle ) ) {
			$csvArray[] = $csvLine;
		}
		return $csvArray;
	}
}
