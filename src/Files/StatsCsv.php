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
	private $now = '';

	/**
	 * StatsCsv constructor.
	 *
	 * @param string $fileName
	 * @param string $now
	 *
	 * @throws \Exception
	 */
	public function __construct( string $fileName, $now ) {
		$fileName = 'stats/' . $fileName;
		$headers = array_merge( [ 'Date' ], self::ELEMENTS );

		$elements = array_keys( self::ELEMENTS );
		$this->data = array_map( function () { return 0; }, $elements );

		parent::__construct( $fileName, $headers );

		$this->now = $now;
	}

	/**
	 * @param string|int $key
	 * @param int $val
	 */
	public function addData( $key, int $val ): void {
		$this->data[ $key ] += $val;
	}

	/**
	 * @param string $now
	 *
	 * @return bool
	 *
	 * @throws \Exception
	 */
	public function putClose( string $now ): bool {
		$row = array_values( $this->data );
		$row = array_merge( [ $now ], $row );
		$this->putRow( $row );
		return $this->close();
	}
}
