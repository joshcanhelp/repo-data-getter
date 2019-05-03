<?php
require 'vendor/autoload.php';

use DxSdk\Data\Files\ReadCsv;

// Date/time to use in file names.
define( 'DATA_SAVE_PATH_SLASHED', dirname(__FILE__) . '/data/' );
define( 'SEPARATOR', '--' );
define( 'DATE_NOW', date( 'Y-m-d_H-i-s' ) );
define( 'TIME_NOW', date( 'U' ) );

if ( empty( $argv[1] ) ) {
	die('No repo');
}

$repoName = $argv[1];
$readCsv = new ReadCsv($repoName);

//
///
////
echo '<pre>' . print_r( $readCsv->getPreviousStats(), TRUE ) . '</pre>';
////
///
//
