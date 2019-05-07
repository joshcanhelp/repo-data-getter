<?php
set_time_limit(0);
date_default_timezone_set ( 'Europe/London' );

require 'vendor/autoload.php';

use DxSdk\Data\Cleaner;
use DxSdk\Data\Logger;

use DxSdk\Data\Api\HttpClient;
use DxSdk\Data\Api\GitHub;

use DxSdk\Data\Files\StatsWriteCsv;
use DxSdk\Data\Files\ReferrerWriteCsv;
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
	$repoNames = Cleaner::repoNamesArray( $repoCsvNames );
} catch ( Exception $e ) {
	$logger->log( 'Failed getting repos from Google CSV: ' . $e->getMessage() )->save();
	exit;
}

// Limit number of repos to process for testing.
if ( ! empty( $argv[1] ) ) {
	$repoNames = array_slice( $repoNames, 0, Cleaner::absint( $argv[1] ) );
}


////
///
// Iterate through all repo names and get GitHub data.
// This will be decoded to use in memory, then combined and stored as the raw JSON.
//
try {
	$globalStatCsv = new StatsWriteCsv( 'global' );
	$referrerCsv = new ReferrerWriteCsv();
} catch ( Exception $e ) {
	$logger->log( 'Failed opening CSV file: ' . $e->getMessage() )->save();
	exit;
}

$orgCsvs = array_flip( Cleaner::orgsFromRepos( $repoNames ) );
foreach( $orgCsvs as $orgName => $value ) {
	$orgCsvs[$orgName] = new StatsWriteCsv( $orgName );
}

$gh = new GitHub( getenv('GITHUB_READ_TOKEN') );
foreach ( $repoNames as $repoName ) {
	$repoFileName = str_replace( '/', SEPARATOR, $repoName );

	try {
		$repoStatCsv = new StatsWriteCsv( $repoFileName );
	} catch ( Exception $e ) {
		$logger->log( sprintf( 'Failed opening CSV file for %s: %s', $repoName, $e->getMessage() ) );
		$repoStatCsv = null;
	}

	try {
		$repoData = [ 'Repo' => $gh->getRepo( $repoName ) ];
	} catch ( Exception $e ) {
		$logger->log( sprintf( 'Failed getting Repo data for %s: %s', $repoName, $e->getMessage() ) );
		$repoData = [ 'Repo' => [] ];
	}

	$repoIsPrivate = isset( $repoData['Repo']['private'] ) && $repoData['Repo']['private'];
	$repoCanPush   = isset( $repoData['Repo']['permissions'] ) && $repoData['Repo']['permissions']['push'];

	// Limited nested
	foreach ( ['Community', 'LatestRelease', 'TrafficClones', 'TrafficRefs', 'TrafficViews'] as $dataType ) {

		// No community profiles if private.
		if ( 'Community' === $dataType && $repoIsPrivate ) {
			continue;
		}

		// No Traffic or Community profile if repo is private or the token is not allowed to push to the repo.
		$dataTypeIsTraffic = in_array( $dataType, ['TrafficClones', 'TrafficRefs', 'TrafficViews'] );
		if ( $dataTypeIsTraffic && ( $repoIsPrivate || ! $repoCanPush ) ) {
			continue;
		}

		try {
			$methodName          = 'get' . $dataType;
			$repoData[$dataType] = $gh->$methodName( $repoName );
		} catch ( Exception $e ) {
			$logMsg = sprintf( 'Failed getting %s data for %s: %s', $dataType, $repoName, $e->getMessage() );
			$logger->log( $logMsg );
			$repoData[$dataType] = [];
		}
	}

	foreach ( StatsWriteCsv::ELEMENTS as $index => $stat ) {
		list( $dataObject, $property ) = explode( SEPARATOR, $stat );

		// Make sure we have a value to add.
		$statValue = 0;
		if ( isset( $repoData[$dataObject][$property] ) ) {
			$statValue = Cleaner::absint( $repoData[$dataObject][$property] );
		}

		$addData = [ $index, $statValue ];
		$globalStatCsv->addData( $addData );
		$orgCsvs[Cleaner::orgName( $repoName )]->addData( $addData );

		if ( $repoStatCsv instanceof StatsWriteCsv ) {
			$repoStatCsv->addData( $addData );
		}
	}

	if ( ! empty( $repoData['TrafficRefs'] ) ) {
		$referrerCsv->addData( $repoData['TrafficRefs'] );
	}

	$jsonFile = new RawJson( $repoFileName );
	$jsonFile->save( $repoData );

	try {
		$repoStatCsv->putClose();
	} catch ( Exception $e ) {
		$logger->log( 'Failed saving stats for ' . $repoName . ': ' . $e->getMessage() );
	}
}

foreach ( $orgCsvs as $orgName => $orgCsv ) {
	try {
		$orgCsv->putClose();
	} catch ( Exception $e ) {
		$logger->log( 'Failed saving stats for ' . $orgName . ': ' . $e->getMessage() );
	}
}

try {
	$globalStatCsv->putClose();
	$referrerCsv->putClose();
} catch ( Exception $e ) {
	$logger->log( 'Failed saving global stats: ' . $e->getMessage() );
}


////
///
// FIN!
//
$logger->save();
exit;
