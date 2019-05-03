<?php
declare(strict_types=1);

namespace DxSdk\Data\Files;

class ReadCsv {

	use Files;

	/**
	 * Csv constructor.
	 *
	 * @param string $fileName
	 *
	 * @throws \Exception
	 */
	public function __construct( string $fileName ) {

		if ( empty( $fileName ) ) {
			throw new \Exception( 'No file name' );
		}

		$this->setReadHandle( 'csv/' . $fileName . '.csv' );
	}

	public function getPreviousStats(): array {
		$csvArray = [];
		while ( $csvLine = fgetcsv( $this->handle ) ) {
			$csvArray[] = $csvLine;
		}

		// Get the CSV headers as keys for the returned array.
		$formattedStats = array_flip( $csvArray[0] );

		// If we only have a single row, then there is no data to pull from.
		// Return 0 for all values.
		if ( 1 === count( $csvArray ) ) {
			return array_map( function () { return 0; }, $formattedStats );
		}

		$lastStats = $csvArray[ count($csvArray) - 1 ];
		foreach ( $formattedStats as $statName => $index ) {
			$formattedStats[$statName] = $lastStats[$index];
		}
		return $formattedStats;
	}
}
