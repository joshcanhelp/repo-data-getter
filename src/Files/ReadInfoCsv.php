<?php
declare(strict_types=1);

namespace DxSdk\Data\Files;

class ReadInfoCsv extends ReadCsv {

	/**
	 * @var string
	 */
	protected $orgName;

	public function __construct( string $orgName ) {
		$this->orgName = $orgName;
		parent::__construct( 'info' . SEPARATOR . $orgName );
	}

	/**
	 * @return array
	 */
	public function getInfoBackup(): array {
		$csvArray = $this->getCsv();
		array_shift( $csvArray );

		$backup = [];
		foreach( $csvArray as $repoInfo ) {
			$backup[$this->orgName . SEPARATOR . $repoInfo[0]] = $repoInfo;
		}

		return $backup;
	}
}
