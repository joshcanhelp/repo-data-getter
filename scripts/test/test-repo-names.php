<?php
require 'vendor/autoload.php';

use DxSdk\Data\Api\HttpClient;
use DxSdk\Data\Files\WriteStatsCsv;
use DxSdk\Data\Cleaner;

define( 'DATA_SAVE_PATH_SLASHED', dirname(__FILE__) . '/test-data/' );

$repoCsvUrl   = getenv('REPO_CSV_URL');
$repoCsvNames = HttpClient::getUrlAsString( $repoCsvUrl );
$repoNames    = Cleaner::repoNamesArray( $repoCsvNames );

//
///
////
echo '<pre>' . print_r( $repoNames, TRUE ) . '</pre>';
////
///
//

die();

$orgCsvs = array_flip( Cleaner::orgsFromRepos( $repoNames ) );

//
///
////
echo '<pre>' . print_r( $orgCsvs, TRUE ) . '</pre>';
////
///
//

foreach( $orgCsvs as $orgName => $value ) {
	$orgCsvs[$orgName] = new WriteStatsCsv( $orgName );
}

//
///
////
echo '<pre>' . print_r( $orgCsvs, TRUE ) . '</pre>';
////
///
//
