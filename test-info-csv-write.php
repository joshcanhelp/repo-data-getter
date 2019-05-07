<?php
require 'vendor/autoload.php';

use DxSdk\Data\Files\InfoWriteCsv;
use DxSdk\Data\Cleaner;

// Date/time to use in file names.
define( 'DATA_SAVE_PATH_SLASHED', dirname(__FILE__) . '/data/' );
define( 'SEPARATOR', '--' );
define( 'DATE_NOW', date( 'Y-m-d_H-i-s' ) );
define( 'TIME_NOW', date( 'U' ) );

if ( empty( $argv[1] ) ) {
	die('No repo');
}

$repoName = $argv[1];
$infoWriteCsv = new InfoWriteCsv(Cleaner::repoFileName($repoName));
$infoWriteCsv->addData( [ 'Repo--description', uniqid() ] );
$infoWriteCsv->addData( [ 'Repo--homepage', uniqid() ] );
$infoWriteCsv->addData( [ 'Repo--topics', uniqid() ] );
$infoWriteCsv->addData( [ 'Repo--license', uniqid() ] );
$infoWriteCsv->addData( [ 'Repo--language', uniqid() ] );
$infoWriteCsv->addData( [ 'Repo--size', uniqid() ] );
$infoWriteCsv->addData( [ 'Repo--pushed_at', uniqid() ] );
$infoWriteCsv->addData( [ 'Repo--created_at', uniqid() ] );
$infoWriteCsv->addData( [ 'Repo--private', uniqid() ] );
$infoWriteCsv->addData( [ 'Repo--html_url', uniqid() ] );
$infoWriteCsv->addData( [ 'Community--health_percentage', uniqid() ] );
$infoWriteCsv->addData( [ 'LatestRelease--name', uniqid() ] );
$infoWriteCsv->addData( [ 'LatestRelease--published_at', uniqid() ] );
$infoWriteCsv->addData( [ 'Coverage', uniqid() ] );
$infoWriteCsv->addData( [ 'CI', uniqid() ] );
$infoWriteCsv->putClose();
//
///
////
//echo '<pre>' . print_r( $infoWriteCsv->out(), TRUE ) . '</pre>';
////
///
//
