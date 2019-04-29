<?php
declare(strict_types=1);

namespace DxSdk\Data\Files;

abstract class Csv {

	use Files;

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

		if ( empty( $fileName ) ) {
			throw new \Exception( 'No file name' );
		}

		if ( ! $this->handle ) {
			$this->setAppendHandle( 'csv/' . $fileName . '.csv' );
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
	 *
	 * @throws \Exception
	 */
	protected function putRow( array $row ): int {
		if ( count( $row ) !== $this->columnCount ) {
			throw new \Exception( 'Number of columns to add does not match number of headers' );
		}
		return $this->putCsv( $row );
	}

	/**
	 * @param $data
	 *
	 * @return mixed
	 */
	abstract public function addData( array $data );

	/**
	 * @return bool
	 */
	abstract public function putClose(): bool;
}
