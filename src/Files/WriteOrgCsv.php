<?php
declare(strict_types=1);

namespace DxSdk\Data\Files;

use DxSdk\Data\Cleaner;

class WriteOrgCsv extends WriteCsv {

	use Files;

	const ELEMENTS = [
		'full_name' => 'orgName',
		'name' => 'text',
		'pushed_at' => 'date',
		'created_at' => 'date',
		'open_issues_count' => 'absint',
		'stargazers_count' => 'absint',
		'forks' => 'absint',
		'archived' => 'absint',
		'html_url' => 'url',
		'description' => 'text',
		'homepage' => 'url',
		'topics' => 'commaSeparateArray',
		'license' . SEPARATOR . 'spdx_id' => 'text',
		'language' => 'text',
	];

	/**
	 * InfoWriteCsv constructor.
	 *
	 * @param string $orgName
	 *
	 * @throws \Exception
	 */
	public function __construct( string $orgName ) {

		// Make sure we don't have any duplicates.
		$headers  = array_keys( self::ELEMENTS );
		$headers  = array_unique( $headers );

		$fileName = 'org-repos' . SEPARATOR . $orgName;
		$this->setWriteHandle( sprintf( self::FILEPATH, $fileName ) );

		parent::__construct( $fileName, $headers );
	}

	/**
	 * @param array $repoData
	 */
	public function addData( array $repoData ) {

		$rowData = [];

		foreach ( self::ELEMENTS as $key => $sanitizeFunc ) {
			$keyParts = explode( SEPARATOR, $key );

			if ( empty( $repoData[$keyParts[0]] ) ) {
				$rowData[] = Cleaner::$sanitizeFunc( '' );
				continue;
			}

			$value = isset($keyParts[1]) ? $repoData[$keyParts[0]][$keyParts[1]] : $repoData[$keyParts[0]];
			$rowData[] = Cleaner::$sanitizeFunc( $value );
		}

		$this->putRow( $rowData );
	}
}
