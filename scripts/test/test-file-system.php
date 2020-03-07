<?php
require 'vendor/autoload.php';

// Date/time to use in file names.
define( 'DATA_SAVE_PATH_SLASHED', dirname(__FILE__) . '/data/' );
define( 'SEPARATOR', '--' );
define( 'DATE_NOW', date( 'Y-m-d_H-i-s' ) );
define( 'TIME_NOW', date( 'U' ) );

$jsonDir = DATA_SAVE_PATH_SLASHED . 'json/';
$allJsonFiles = scandir( $jsonDir, SCANDIR_SORT_DESCENDING );
$repoJsonFiles = array_filter( $allJsonFiles, function( $el ) { global $argv; return ( 0 === strpos( $el, $argv[1] ) ); } );
$repoJsonFiles = array_values( $repoJsonFiles );

//echo file_get_contents( $repoJsonFiles[0] );

//
///
////
echo '<pre>' . print_r( $repoJsonFiles, TRUE ) . '</pre>';
////
///
//
