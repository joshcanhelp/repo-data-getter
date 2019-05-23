<?php

namespace DxSdk\Data\Files;

class WriteJson {

	use Files;

	const FILEPATH = 'json/%s.json';

	public function __construct( string $fileName ) {
		$this->setWriteHandle( sprintf( self::FILEPATH, $fileName . SEPARATOR . DATE_NOW ) );
	}

	/**
	 * @param array $data
	 */
	public function save( array $data ) {
		$this->write( json_encode( $data ) );
		$this->close();
	}
}
