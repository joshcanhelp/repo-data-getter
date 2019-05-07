<?php
require 'vendor/autoload.php';

use DxSdk\Data\Api\HttpClient;
use DxSdk\Data\Files\StatsWriteCsv;
use DxSdk\Data\Cleaner;

define( 'DATA_SAVE_PATH_SLASHED', dirname(__FILE__) . '/test-data/' );

$repoCsvUrl   = 'https://docs.google.com/spreadsheets/d/e/'
                . '2PACX-1vSpmsvxKi7yLE__SF16E3T3UEHzuLNprBDzK6nQofBDYwFMBcfv89WIX_2JLfM7EQkRjCEC7g6P8Vwd'
                . '/pub?gid=391421749&single=true&output=csv';

$repoCsvNames = HttpClient::getUrlAsString( $repoCsvUrl );
$repoNames = explode( PHP_EOL, $repoCsvNames ) ;
$repoNames = Cleaner::repoNamesArray( $repoNames );

//
///
////
echo '<pre>' . print_r( $repoNames, TRUE ) . '</pre>';
////
///
//

$orgCsvs = array_flip( Cleaner::orgsFromRepos( $repoNames ) );

//
///
////
echo '<pre>' . print_r( $orgCsvs, TRUE ) . '</pre>';
////
///
//

foreach( $orgCsvs as $orgName => $value ) {
	$orgCsvs[$orgName] = new StatsWriteCsv( $orgName );
}

//
///
////
echo '<pre>' . print_r( $orgCsvs, TRUE ) . '</pre>';
////
///
//
