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
		$fileName = 'stats/' . $fileName;
		$headers = array_merge( [ 'Date' ], self::ELEMENTS );

		$elements = array_flip( self::ELEMENTS );
		$this->data = array_map( function () { return 0; }, $elements );

		parent::__construct( $fileName, $headers );
	}

	/**
	 * @param string $key
	 * @param int $val
	 *
	 * @throws \Exception
	 */
	public function addData( string $key, int $val ): void {
		if ( ! isset( $this->data[ $key ] ) ) {
			$this->data[ $key ] = 0;
		}
		$this->data[ $key ] += $val;
	}

	/**
	 * @param array $row
	 * @param string $now
	 *
	 * @throws \Exception
	 */
	public function putClose( array $row, string $now ): void {
		array_unshift( $row, $now );
		$this->putRow( $row );
		$this->close();
	}
}
