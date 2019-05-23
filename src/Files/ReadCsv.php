<?php
declare(strict_types=1);

namespace DxSdk\Data\Files;

use DxSdk\Data\Cleaner;

class ReadCsv {

	use Files;

	/**
	 * @var string
	 */
	protected $fileName;

	/**
	 * ReadCsv constructor.
	 *
	 * @param string $repoName - Full repo name including org.
	 */
	public function __construct( string $repoName ) {
		$fileName = Cleaner::repoFileName( $repoName );
		$this->fileName = $fileName;
		$this->setReadHandle( sprintf( WriteCsv::FILEPATH, $fileName ) );
	}

	/**
	 * @return string
	 */
	public function getFileName() {
		return $this->fileName;
	}

	/**
	 * @return array
	 */
	public function getCsv(): array {
		rewind( $this->handle );

		$csvArray = [];
		while ( $csvLine = fgetcsv( $this->handle ) ) {
			$csvArray[] = $csvLine;
		}
		return $csvArray;
	}
}
