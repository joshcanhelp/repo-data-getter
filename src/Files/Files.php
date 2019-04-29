<?php

declare(strict_types=1);

namespace DxSdk\Data\Files;

trait Files {

	private $handle;
	private $saveTo;

	protected function close(): bool {
		return fclose( $this->handle );
	}

	private function setAppendHandle( string $relPath ) {
		$this->handle = $this->getHandle( $relPath, 'a' );
	}

	private function setWriteHandle( string $relPath ) {
		$this->handle = $this->getHandle( $relPath, 'w' );
	}

	private function getHandle( string $relPath, string $mode ) {
		$this->saveTo = DATA_SAVE_PATH_SLASHED . $relPath;
		return fopen( $this->saveTo, $mode );
	}

	private function writeToFile( string $path, string $data ) {
		$this->setWriteHandle( $path );
		$this->write( $data );
		return (bool) $this->close();
	}

	private function write( string $data ) {
		return fwrite( $this->handle, $data );
	}

	private function putCsv( $row ) {
		return fputcsv( $this->handle, $row );
	}
}
