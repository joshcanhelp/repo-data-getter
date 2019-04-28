<?php
declare(strict_types=1);

namespace DxSdk\Data\Files;

class Csv {

	private $saveTo;
	private $handle;
	private $columnCount;

	/**
	 * Csv constructor.
	 *
	 * @param string $fileName
	 * @param array $headers
	 *
	 * @throws \Exception
	 */
	public function __construct( string $fileName, array $headers = [] ) {

		if ( empty( $fileName ) ) {
			throw new \Exception( 'No file name' );
		}

		$this->saveTo = 'csv/' . $fileName . '.csv';
		$this->handle = fopen( $this->saveTo, 'a' );

		$this->columnCount = count( $headers );
		if ( $this->columnCount && ! filesize( $this->saveTo ) ) {
			fputcsv( $this->handle, $headers );
		}
	}

	/**
	 * @param array $row
	 *
	 * @return bool
	 *
	 * @throws \Exception
	 */
	public function putRow( array $row ): bool {
		if ( count( $row ) !== $this->columnCount ) {
			throw new \Exception( 'Number of columns to add does not match number of headers' );
		}
		return fputcsv( $this->handle, $row );
	}

	/**
	 * @return bool
	 */
	public function close(): bool {
		return fclose( $this->handle );
	}
}
