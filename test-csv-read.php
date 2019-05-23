<?php
require 'vendor/autoload.php';

use DxSdk\Data\Cleaner;
use DxSdk\Data\Files\ReadJson;

// Date/time to use in file names.
define( 'DATA_SAVE_PATH_SLASHED', dirname(__FILE__) . '/data/' );
define( 'SEPARATOR', '--' );
define( 'DATE_NOW', date( 'Y-m-d_H-i-s' ) );
define( 'TIME_NOW', date( 'U' ) );

if ( empty( $argv[1] ) ) {
	die('No repo');
}

//
///
////

echo '<pre>' . print_r( Cleaner::jsonDecode( ReadJson::read( $argv[1] ) ), TRUE ) . '</pre>';
////
///
//
