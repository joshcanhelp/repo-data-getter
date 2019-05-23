<?php
declare(strict_types=1);

namespace DxSdk\Data\Files;

use DxSdk\Data\Cleaner;

class WriteReferrerCsv extends WriteCsv {

	use Files;

	private $data = [];

	/**
	 * StatsCsv constructor.
	 *
	 * @throws \Exception
	 */
	public function __construct() {
		$fileName = 'stats' . SEPARATOR . 'referrers';
		$this->setWriteHandle( sprintf( self::FILEPATH, $fileName ) );
		$headers = [ 'Referrer', 'Count', 'Uniques' ];
		parent::__construct( $fileName, $headers );
	}

	/**
	 * @param array $addData
	 */
	public function addData( array $addData ) {
		foreach( $addData as $referrer ) {
			$name = Cleaner::text( $referrer['referrer'] );
			$count = Cleaner::absint( $referrer['count'] );
			$uniques = Cleaner::absint( $referrer['uniques'] );

			if ( ! isset( $this->data[ $name ] ) ) {
				$this->data[$name] = [ 'count' => 0, 'uniques' => 0 ];
			}

			$this->data[$name]['count'] += $count;
			$this->data[$name]['uniques'] += $uniques;
		}
	}

	/**
	 * @return bool
	 */
	public function putClose(): bool {
		$rows = $this->data;
		uasort( $rows, [ $this, 'sortByCount' ] );
		foreach ( $rows as $name => $data ) {
			$this->putRow( [ $name, $data['count'], $data['uniques'] ] );
		}
		return $this->close();
	}

	/**
	 * @param $a
	 * @param $b
	 *
	 * @return int
	 */
	private function sortByCount($a, $b) {
		if ($a['count'] == $b['count']) {
			return 0;
		}
		return ( $a['count'] > $b['count'] ) ? -1 : 1;
	}
}
