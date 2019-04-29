<?php
declare(strict_types=1);

namespace DxSdk\Data\Files;

use DxSdk\Data\Cleaner;
use function foo\func;

class ReferrerCsv extends Csv {

	private $data = [];

	/**
	 * StatsCsv constructor.
	 *
	 * @throws \Exception
	 */
	public function __construct() {
		$fileName = 'stats/all-referrers';
		$headers = [ 'Referrer', 'Count', 'Uniques' ];
		parent::__construct( $fileName, $headers );
	}

	/**
	 * @param array $refArray
	 */
	public function addData( array $refArray ) {
		foreach( $refArray as $referrer ) {
			$name = Cleaner::text( $referrer['referrer'] );
			$count = Cleaner::absint( $referrer['count'] );
			$uniques = Cleaner::absint( $referrer['uniques'] );
			if ( isset( $this->data[ $name ] ) ) {
				$this->data[$name]['count'] += $count;
				$this->data[$name]['uniques'] += $uniques;
			} else {
				$this->data[$name] = [
					'count' => $count,
					'uniques' => $uniques,
				];
			}
		}
	}

	public function getData() {
		return $this->data;
	}

	/**
	 *
	 * @throws \Exception
	 */
	public function putClose() {
		$rows = $this->data;
		uasort( $rows, [ $this, 'sortByCount' ] );
		foreach ( $rows as $name => $data ) {
			$this->putRow( [ $name, $data['count'], $data['uniques'] ] );
		}
		return $this->close();
	}

	private function sortByCount($a, $b) {
		if ($a['count'] == $b['count']) {
			return 0;
		}
		return ( $a['count'] > $b['count'] ) ? -1 : 1;
	}
}
