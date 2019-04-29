<?php
declare(strict_types=1);

namespace DxSdk\Data\Files;

class StatsCsv extends Csv {

	const ELEMENTS = [
		'Repo|stargazers_count', 'Repo|subscribers_count', 'Repo|forks', 'Repo|open_issues_count',
		'TrafficClones|count', 'TrafficClones|uniques',
		'TrafficViews|count', 'TrafficViews|uniques',
	];

	private $data = [];

	/**
	 * StatsCsv constructor.
	 *
	 * @param string $fileName
	 *
	 * @throws \Exception
	 */
	public function __construct( string $fileName ) {
		$headers = array_merge( [ 'Date' ], self::ELEMENTS );
		$elements = array_keys( self::ELEMENTS );
		$this->data = array_map( function () { return 0; }, $elements );
		parent::__construct( $fileName, $headers );
	}

	/**
	 * @param array $addData
	 */
	public function addData( array $addData ) {
		$this->data[$addData[0]] += $addData[1];
	}

	/**
	 * @return bool
	 *
	 * @throws \Exception
	 */
	public function putClose(): bool {
		$row = array_values( $this->data );
		$row = array_merge( [ DATE_NOW ], $row );
		$this->putRow( $row );
		return $this->close();
	}
}
