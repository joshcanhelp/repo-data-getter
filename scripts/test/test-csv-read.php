<?php
require 'vendor/autoload.php';

use Dotenv\Dotenv;
use DxSdk\Data\Cleaner;
use DxSdk\Data\Api\HttpClient;

// Date/time to use in file names.
define( 'DATA_SAVE_PATH_SLASHED', dirname(__FILE__) . '/data/' );
define( 'SEPARATOR', '--' );
define( 'DATE_NOW', date( 'Y-m-d_H-i-s' ) );
define( 'TIME_NOW', date( 'U' ) );

$dotenv = Dotenv::create(__DIR__);
$dotenv->load();

$repoCsvUrl = getenv('REPO_CSV_URL');

//
///
////
$repoCsvNames = HttpClient::getUrlAsString( $repoCsvUrl );
$repoNames = Cleaner::repoNamesArray( $repoCsvNames );
echo '<pre>' . print_r( $repoNames, TRUE ) . '</pre>';
////
///
//
