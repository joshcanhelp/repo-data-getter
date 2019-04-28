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
	$logger->log( 'Getting repos from Google CSV ...' );
	$repoCsvNames = HttpClient::justGet( $repoCsvUrl );
	$logger->log( 'Got ' . strlen( $repoCsvNames ) . ' bytes' );
} catch ( Exception $e ) {
	$logger->log( 'Failed getting repos from Google CSV: ' . $e->getMessage() );
	exit;
}


////
///
// Split CSV on new lines
// Repo name is in the first column and is the only thing we need
//
$repoNames = explode( PHP_EOL, $repoCsvNames );

// Filter out empty rows and rows without a slash
$repoNames = array_filter( $repoNames, function ( $el ) {
	return ! empty( $el ) && strpos( $el, '/' );
} );

// Reduce text down to first column
$repoNames = array_map( function ( $el ) {
	return explode( ',', $el )[0];
}, $repoNames );
$logger->log( 'Have ' . count( $repoNames ) . ' repo names' );


////
///
// Iterate through all repo names and get GitHub data.
// This will be decoded to use in memory, then combined and stored as the raw JSON.
//
$allRepoData = [];
$gh = new GitHub( getenv('GITHUB_READ_TOKEN') );
foreach ( $repoNames as $repoName ) {

	$allRepoData[$repoName] = [];
	foreach ( ['Repo', 'Community', 'LatestRelease', 'TrafficClones', 'TrafficPaths', 'TrafficViews'] as $dataType ) {

		// Community profiles do not exist for private repos.
		// Skip to next data type.
		if ( 'Community' === $dataType && $allRepoData[$repoName]['Repo']['private'] ) {
			continue;
		}

		try {
			$logger->log( sprintf( 'Getting %s data for %s from GitHub ...', $dataType, $repoName ) );

			$methodName              = 'get' . $dataType;
			$dataRaw                 = $gh->$methodName( $repoName );
			$allRepoData[$repoName][$dataType] = json_decode( $dataRaw, true );

			$logger->log( sprintf( 'Getting %s data for %s from GitHub ...', strlen( $dataRaw ), $dataType, $repoName ) );
		} catch ( Exception $e ) {
			$logger->log( sprintf( 'Failed getting %s data for %s from GitHub: %s', $dataType, $repoName, $e->getMessage() ) );
			$allRepoData[$repoName][$dataType] = '{}';
		}
	}

	$fileName = str_replace( '/', '|', $repoName ) . '--' . $now . '.json';
	$logger->log( 'Saving to ' . $fileName );
	file_put_contents( 'json/' . $fileName, json_encode( $allRepoData[$repoName] ) );
}


////
///
// Iterate through the decoded data to store per-repo stats and all repo stats.
//

$statCsvElements = StatsCsv::ELEMENTS;
$globalStatCsv = new StatsCsv( 'global' );

$globalStats = [];
$orgStats = [];
foreach ( $allRepoData as $repoName => $data ) {

	$orgName = explode( '/', $repoName )[0];
	if ( ! isset( $orgStats[$orgName] ) ) {
		$orgStats[$orgName] = [];
	}

	$repoStatCsv = new StatsCsv( str_replace( '/', '|', $repoName ) );

	$logger->log( 'Parsing stats for ' . $repoName );
	foreach ( $statCsvElements as $index => $stat ) {
		$statKey = explode( '|', $stat );

		// Make sure we have something to add.
		$statValue = isset( $data[$statKey[0]][$statKey[1]] ) ? $data[$statKey[0]][$statKey[1]] : 0;
		$statValue = Cleaner::absint( $statValue );

		// Add to the global running total.
		if ( isset( $globalStats[$index] ) ) {
			$globalStats[$index] = 0;
		}
		$globalStats[$index] += $statValue;

		// Add to the org running total.
		if ( ! isset( $orgStats[$orgName][$index] ) ) {
			$orgStats[$orgName][$index] = 0;
		}
		$orgStats[$orgName][$index] += $statValue;

		// Add to the repo stats.
		$repoStats[$index] = $statValue;
	}
	$logger->log( 'Saving to ' . $repoCsvFileName );
	fputcsv( $repoCsvHandle, array_merge( $statCsvElements, ['date'] ) );
	fclose( $repoCsvHandle );
}

foreach ( $orgStats as $orgName => $orgStat ) {
	$orgCsvFileName = 'csv/stats/' . $orgName . '.csv';
	$orgCsvHandle   = fopen( $orgCsvFileName, 'a' );
	if ( ! filesize( $orgCsvFileName ) ) {
		fputcsv( $orgCsvHandle, array_merge( $statCsvElements, ['date'] ) );
	}
	$logger->log( 'Saving to ' . $orgCsvFileName );
	fputcsv( $orgCsvHandle, [$now] + $orgStat );
	fclose( $orgCsvHandle );
}

$logger->log( 'Saving to ' . $globalCsvFileName );
fputcsv( $globalCsvHandle, [$now] + $globalStats );
fclose( $globalCsvHandle );


////
///
// FIN!
//
$timeTaken = 'Done in ' . round( microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'], 1 ) . 's';
$logger->log( $timeTaken );
$logger->save();
echo $timeTaken;
exit;


//
///
//// STORAGE
///
//

// Sanitize repo data for CSV storage
$repo = json_decode( $thisRepoData['repo'], true );
$repoParsed = [
	// Information
	'private' => ! empty( $repo['private'] ),
	'html_url' => empty( $repo['html_url'] ) ? '' : Cleaner::url( $repo['html_url'] ),
	'description' => empty( $repo['description'] ) ? '' : Cleaner::text( $repo['description'] ),
	'topics' => empty( $repo['topics'] ) ? '' : implode( ', ', Cleaner::textArray( $repo['topics'] ) ),
	'language' => empty( $repo['language'] ) ? '' : Cleaner::text( $repo['language'] ),
	'license' => empty( $repo['license'] ) || empty( $repo['license']['spdx_id'] ) ?
		'Unknown' :
		Cleaner::text( $repo['license']['spdx_id'] ),

	// Stats
	'stargazers_count' => empty( $repo['stargazers_count'] ) ? 0 : Cleaner::absint( $repo['stargazers_count'] ),
	'subscribers_count' => empty( $repo['subscribers_count'] ) ? 0 : Cleaner::absint( $repo['subscribers_count'] ),
	'forks' => empty( $repo['forks'] ) ? 0 : Cleaner::absint( $repo['forks'] ),
	'open_issues_count' => empty( $repo['open_issues_count'] ) ? 0 : Cleaner::absint( $repo['open_issues_count'] ),
	'size' => empty( $repo['size'] ) ? 0 : Cleaner::absint( $repo['size'] ),

	// Dates
	'created_at' => empty( $repo['created_at'] ) ? '' : Cleaner::date( $repo['created_at'] ),
	'updated_at' => empty( $repo['updated_at'] ) ? '' : Cleaner::date( $repo['updated_at'] ),
	'pushed_at' => empty( $repo['pushed_at'] ) ? '' : Cleaner::date( $repo['pushed_at'] ),
];

// TODO: Remove debugging
echo "\n" . print_r( $repoParsed, TRUE ) . "\n";
