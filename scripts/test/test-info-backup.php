<?php
require 'vendor/autoload.php';

use DxSdk\Data\Files\ReadInfoCsv;

// Date/time to use in file names.
define( 'DATA_SAVE_PATH_SLASHED', dirname(__FILE__) . '/data/' );
define( 'SEPARATOR', '--' );
define( 'DATE_NOW', date( 'Y-m-d_H-i-s' ) );
define( 'TIME_NOW', date( 'U' ) );


$readCsv = new ReadInfoCsv('info--auth0');

//
///
////
echo '<pre>' . print_r( $readCsv->getInfoBackup(), TRUE ) . '</pre>';
////
///
//
