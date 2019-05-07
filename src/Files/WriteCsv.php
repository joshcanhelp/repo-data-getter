<?php
declare(strict_types=1);

namespace DxSdk\Data\Files;

abstract class WriteCsv {

	use Files;

	const FILEPATH = 'csv/%s.csv';

	protected $columnCount;

	/**
	 * Csv constructor.
	 *
	 * @param string $fileName
	 * @param array $headers
	 *
	 * @throws \Exception
	 */
	protected function __construct( string $fileName, array $headers = [] ) {

		if ( ! $this->handle ) {
			$this->setAppendHandle( sprintf( self::FILEPATH, $fileName ) );
		}

		$this->columnCount = count( $headers );
		if ( $this->columnCount && ! filesize( $this->saveTo ) ) {
			$this->putCsv( $headers );
		}
	}

	/**
	 * @param array $row
	 *
	 * @return int
	 */
	protected function putRow( array $row ): int {
		return $this->putCsv( $row );
	}

	/**
	 * @param $data
	 *
	 * @return mixed
	 */
	abstract public function addData( array $data );
}
