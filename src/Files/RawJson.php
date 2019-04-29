<?php

namespace DxSdk\Data\Files;

class RawJson {

	use Files;

	public function __construct( string $fileName ) {
		$this->setWriteHandle( 'json/'. $fileName . '--' . DATE_NOW . '.json' );
	}

	/**
	 * @param array $data
	 */
	public function save( array $data ) {
		$this->write( json_encode( $data ) );
		$this->close();
	}
}
