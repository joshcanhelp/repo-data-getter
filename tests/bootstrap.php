<?php

define( 'SEPARATOR', '--' );
define( 'DATA_SAVE_PATH_SLASHED', dirname(__FILE__) . '/test-data/' );
define( 'DATE_NOW', 'DATENOW' );
define( 'TIME_NOW', 'TIMENOW' );

use \DxSdk\Data\Files\WriteStatsCsv;
use \DxSdk\Data\Files\WriteInfoCsv;
use \DxSdk\Data\Files\WriteJson;

function dxsdk_tests_make_stats_csv( $fileName, $withData = true ) {
	$fh = fopen( $fileName, 'w' );
	$headers = array_merge( [ 'Date UTC', 'Timecode UTC' ], WriteStatsCsv::ELEMENTS );
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

function dxsdk_tests_make_json( $fileName ) {
	$dataProps = array_merge( array_keys( WriteInfoCsv::ELEMENTS ), WriteStatsCsv::ELEMENTS );
	$jsonData = [];
	foreach( $dataProps as $dataType ) {
		$dataTypeParts = explode( SEPARATOR, $dataType );

		if ( ! isset( $jsonData[ $dataTypeParts[0] ] ) ) {
			$jsonData[ $dataTypeParts[0] ] = [];
		}

		if ( ! isset( $dataTypeParts[1] ) ) {
			$jsonData[ $dataTypeParts[0] ] = mt_rand( 0, 100 );
			continue;
		}

		$jsonData[ $dataTypeParts[0] ][ $dataTypeParts[1] ] = mt_rand( 0, 100 );

	}

	$writeJson = new WriteJson( $fileName );
	$writeJson->save( $jsonData );
}
