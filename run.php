<?php
set_time_limit(0);
date_default_timezone_set ( 'Europe/London' );

require 'vendor/autoload.php';

use DxSdk\Data\Cleaner;
use DxSdk\Data\Logger;

use DxSdk\Data\Api\HttpClient;
use DxSdk\Data\Api\GitHub;

use DxSdk\Data\Files\StatsCsv;
use DxSdk\Data\Files\ReferrerCsv;
use DxSdk\Data\Files\RawJson;

use Dotenv\Dotenv;

$dotenv = Dotenv::create(__DIR__);
$dotenv->load();

// Date/time to use in file names.
define( 'DATA_SAVE_PATH_SLASHED', dirname(__FILE__) . '/data/' );
define( 'SEPARATOR', '--' );
define( 'DATE_NOW', date( 'Y-m-d_H-i-s' ) );
define( 'TIME_NOW', date( 'U' ) );

$logger = new Logger();

////
///
// Get all repos from the matrix spreadsheet.
// All repos are stored in the "All Repos" sheet and updated with every sheet update.
// That sheet is published as CSV from File > Publish to Web
//
$repoCsvUrl   = 'https://docs.google.com/spreadsheets/d/e/'
                . '2PACX-1vSpmsvxKi7yLE__SF16E3T3UEHzuLNprBDzK6nQofBDYwFMBcfv89WIX_2JLfM7EQkRjCEC7g6P8Vwd'
                . '/pub?gid=391421749&single=true&output=csv';
try {
	$repoCsvNames = HttpClient::justGet( $repoCsvUrl );
} catch ( Exception $e ) {
	$logger->log( 'Failed getting repos from Google CSV: ' . $e->getMessage() )->save();
	exit;
}
$repoNames = explode( PHP_EOL, $repoCsvNames ) ;
$repoNames = Cleaner::repoNamesArray( $repoNames );

// Limit number of repos to process for testing.
if ( ! empty( $argv[1] ) ) {
	$repoNames = array_slice( $repoNames, 0, Cleaner::absint( $argv[1] ) );
}


////
///
// Iterate through all repo names and get GitHub data.
// This will be decoded to use in memory, then combined and stored as the raw JSON.
//
$gh = new GitHub( getenv('GITHUB_READ_TOKEN') );

try {
	$globalStatCsv = new StatsCsv( 'global' );
	$referrerCsv = new ReferrerCsv();
} catch ( \Exception $e ) {
	$logger->log( 'Failed opening CSV file: ' . $e->getMessage() )->save();
	exit;
}

$orgCsvs = array_flip( Cleaner::orgsFromRepos( $repoNames ) );
foreach( $orgCsvs as $orgName => $value ) {
	$orgCsvs[$orgName] = new StatsCsv( $orgName );
}

foreach ( $repoNames as $repoName ) {
	$orgName = Cleaner::orgName( $repoName );
	$repoFileName = str_replace( '/', SEPARATOR, $repoName );
	$repoStatCsv = new StatsCsv( $repoFileName );

	$repoData = [];
	foreach ( ['Repo', 'Community', 'LatestRelease', 'TrafficClones', 'TrafficRefs', 'TrafficViews'] as $dataType ) {

		// Community profiles do not exist for private repos; skip to next data type.
		if ( 'Community' === $dataType && $repoData['Repo']['private'] ) {
			continue;
		}

		// No traffic tracking needed for private repos.
		if ( 0 === strpos( 'Traffic', $dataType ) && $repoData['Repo']['private'] ) {
			continue;
		}

		try {
			$methodName              = 'get' . $dataType;
			$dataRaw                 = $gh->$methodName( $repoName );
			$repoData[$dataType] = json_decode( $dataRaw, true );
		} catch ( Exception $e ) {
			$logMsg = sprintf( 'Failed getting %s data for %s: %s', $dataType, $repoName, $e->getMessage() );
			$logger->log( $logMsg );
			$repoData[$dataType] = [];
		}
	}

	foreach ( StatsCsv::ELEMENTS as $index => $stat ) {
		$statKeyParts = explode( SEPARATOR, $stat );
		$dataObject = $statKeyParts[0];
		$property = $statKeyParts[1];

		// Make sure we have something to add.
		$statValue = 0;
		if ( isset( $repoData[$dataObject][$property] ) ) {
			$statValue = Cleaner::absint( $repoData[$dataObject][$property] );
		}

		$addData = [ $index, $statValue ];
		$globalStatCsv->addData( $addData );
		$orgCsvs[$orgName]->addData( $addData );
		$repoStatCsv->addData( $addData );
	}

	if ( ! empty( $repoData['TrafficRefs'] ) ) {
		$referrerCsv->addData( $repoData['TrafficRefs'] );
	}

	$jsonFile = new RawJson( $repoFileName );
	$jsonFile->save( $repoData );

	try {
		$repoStatCsv->putClose();
	} catch ( \Exception $e ) {
		$logger->log( 'Failed saving stats for ' . $repoName . ': ' . $e->getMessage() );
	}
}

foreach ( $orgCsvs as $orgName => $orgCsv ) {
	try {
		$orgCsv->putClose();
	} catch ( \Exception $e ) {
		$logger->log( 'Failed saving stats for ' . $orgName . ': ' . $e->getMessage() );
	}
}

try {
	$globalStatCsv->putClose();
	$referrerCsv->putClose();
} catch ( \Exception $e ) {
	$logger->log( 'Failed saving global stats: ' . $e->getMessage() );
}


////
///
// FIN!
//
$logger->save();
exit;
