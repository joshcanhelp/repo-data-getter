<?php

define( 'SEPARATOR', '--' );
define( 'DATA_SAVE_PATH_SLASHED', dirname(__FILE__) . '/test-data/' );

function dxsdk_tests_make_stats_csv( $fileName, $withData = true ) {
	$fh = fopen( $fileName, 'w' );
	$headers = array_merge( [ 'Date UTC', 'Timecode UTC' ], \DxSdk\Data\Files\WriteStatsCsv::ELEMENTS );
	fputcsv( $fh, $headers );

	if ( $withData ) {
		$row1 = $row2 = [];
		for ( $i = 1; $i <= count( $headers ); $i++ ) {
			$row1[] = mt_rand( 10, 1000 );
			$row2[] = mt_rand( 10, 1000 );
		}

		fputcsv( $fh, $row1 );
		fputcsv( $fh, $row2 );
	}

	fclose( $fh );
}
