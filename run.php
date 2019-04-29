<?php
set_time_limit(0);

require 'vendor/autoload.php';

use DxSdk\Data\Api\HttpClient;
use DxSdk\Data\Api\GitHub;
use DxSdk\Data\Files\StatsCsv;
use DxSdk\Data\Cleaner;
use DxSdk\Data\Logger;

use Dotenv\Dotenv;

$dotenv = Dotenv::create(__DIR__);
$dotenv->load();

// Date/time to use in file names.
$now = date( 'Y-m-d\TU' );

$logger = new Logger( $now );

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
	$logger->log( 'Failed getting repos from Google CSV: ' . $e->getMessage() );
	exit;
}
$repoNames = explode( PHP_EOL, $repoCsvNames ) ;
$repoNames = Cleaner::repoNamesArray( $repoNames );


////
///
// Iterate through all repo names and get GitHub data.
// This will be decoded to use in memory, then combined and stored as the raw JSON.
//
$allRepoData = [];
$orgNames = [];
$gh = new GitHub( getenv('GITHUB_READ_TOKEN') );

foreach ( $repoNames as $repoName ) {
	$orgNames[] = Cleaner::orgName( $repoName );
	$allRepoData[$repoName] = [];

	foreach ( ['Repo', 'Community', 'LatestRelease', 'TrafficClones', 'TrafficPaths', 'TrafficViews'] as $dataType ) {
		// Community profiles do not exist for private repos.
		// Skip to next data type.
		if ( 'Community' === $dataType && $allRepoData[$repoName]['Repo']['private'] ) {
			continue;
		}

		try {
			$methodName              = 'get' . $dataType;
			$dataRaw                 = $gh->$methodName( $repoName );
			$allRepoData[$repoName][$dataType] = json_decode( $dataRaw, true );
		} catch ( Exception $e ) {
			$logger->log( sprintf( 'Failed getting %s data for %s: %s', $dataType, $repoName, $e->getMessage() ) );
			$allRepoData[$repoName][$dataType] = [];
		}
	}

	$fileName = str_replace( '/', '|', $repoName ) . '--' . $now . '.json';
	$logger->log( 'Saving to ' . $fileName );
	file_put_contents( 'json/' . $fileName, json_encode( $allRepoData[$repoName] ) );
}
$orgNames = array_unique( $orgNames );


////
///
// Iterate through the decoded data to store per-repo stats and all repo stats.
//
$globalStatCsv = new StatsCsv( 'global', $now );

$orgCsvs = [];
foreach ( $orgNames as $orgName ) {
	$orgCsvs[$orgName] = new StatsCsv( $orgName, $now );
}

foreach ( $allRepoData as $repoName => $repoData ) {
	$orgName = explode( '/', $repoName )[0];
	$repoStatCsv = new StatsCsv( str_replace( '/', '|', $repoName ), $now );

	foreach ( StatsCsv::ELEMENTS as $index => $stat ) {
		$statKeyParts = explode( '|', $stat );
		$dataObject = $statKeyParts[0];
		$property = $statKeyParts[1];

		// Make sure we have something to add.
		$statValue = 0;
		if ( isset( $repoData[$dataObject][$property] ) ) {
			$statValue = Cleaner::absint( $repoData[$dataObject][$property] );
		}

		$globalStatCsv->addData( $index, $statValue );
		$orgCsvs[$orgName]->addData( $index, $statValue );
		$repoStatCsv->addData( $index, $statValue );
	}

	try {
		$repoStatCsv->putClose( $now );
	} catch ( \Exception $e ) {
		$logger->log( 'Failed saving stats for ' . $repoName . ': ' . $e->getMessage() );
	}
}

foreach ( $orgCsvs as $orgName => $orgCsv ) {
	try {
		$orgCsv->putClose( $now );
	} catch ( \Exception $e ) {
		$logger->log( 'Failed saving stats for ' . $orgName . ': ' . $e->getMessage() );
	}
}

try {
	$globalStatCsv->putClose( $now );
} catch ( \Exception $e ) {
	$logger->log( 'Failed saving global stats: ' . $e->getMessage() );
}


////
///
// FIN!
//
$logger->save();
exit;
