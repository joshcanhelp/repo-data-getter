<?php
set_time_limit(0);

require 'vendor/autoload.php';

use DxSdk\Data\Cleaner;

$now = date( 'Y-m-d\TU' );

$allRepoData = [
	'org1/name1' => [
		'Repo' => [
			'stargazers_count' => mt_rand( 10, 90 ),
			'subscribers_count' => mt_rand( 10, 90 ),
			'forks' => mt_rand( 10, 90 ),
			'open_issues_count' => mt_rand( 10, 90 ),
		],
		'TrafficClones' => [
			'count' => mt_rand( 10, 90 ),
			'uniques' => mt_rand( 10, 90 ),
		],
		'TrafficViews' => [
			'count' => mt_rand( 100, 900 ),
			'uniques' => mt_rand( 100, 900 ),
		],
	],
	'org2/name2' => [
		'Repo' => [
			'stargazers_count' => mt_rand( 10, 90 ),
			'subscribers_count' => mt_rand( 10, 90 ),
			'forks' => mt_rand( 10, 90 ),
			'open_issues_count' => mt_rand( 10, 90 ),
		],
		'TrafficClones' => [
			'count' => mt_rand( 10, 90 ),
			'uniques' => mt_rand( 10, 90 ),
		],
		'TrafficViews' => [
			'count' => mt_rand( 100, 900 ),
			'uniques' => mt_rand( 100, 900 ),
		],
	],
];

$statCsvHeaders = [
	'Repo|stargazers_count', 'Repo|subscribers_count', 'Repo|forks', 'Repo|open_issues_count',
	'TrafficClones|count', 'TrafficClones|uniques',
	'TrafficViews|count', 'TrafficViews|uniques',
];

$globalCsvFileName = 'csv/stats/global.csv';
$globalCsvHandle   = fopen( $globalCsvFileName, 'a' );
if ( ! filesize( $globalCsvFileName ) ) {
	fputcsv( $globalCsvHandle, ['date'] + $statCsvHeaders );
}

$globalStats = [];
foreach ( $allRepoData as $repoName => $data ) {

	$repoStats = [];
	$repoCsvFileName = 'csv/stats/' . str_replace( '/', '|', $repoName ) . '.csv';
	$repoCsvHandle   = fopen( $repoCsvFileName, 'a' );
	if ( ! filesize( $repoCsvFileName ) ) {
		fputcsv( $repoCsvHandle, ['date'] + $statCsvHeaders );
	}

	foreach ( $statCsvHeaders as $index => $stat ) {
		$statKey = explode( '|', $stat );
		$globalStats[$index] = isset( $globalStats[$index] ) ? $globalStats[$index] : 0;
		$globalStats[$index] += Cleaner::absint( $data[$statKey[0]][$statKey[1]] );
		$repoStats[$index] = $globalStats[$index];
	}

	fputcsv($repoCsvHandle, [$now] + $repoStats);
}
fputcsv($globalCsvHandle, [$now] + $globalStats);
