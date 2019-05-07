<?php
set_time_limit(0);
date_default_timezone_set ( 'Europe/London' );

require 'vendor/autoload.php';

use DxSdk\Data\Cleaner;
use DxSdk\Data\Logger;

use DxSdk\Data\Api\HttpClient;
use DxSdk\Data\Api\GitHub;

use DxSdk\Data\Files\StatsWriteCsv;
use DxSdk\Data\Files\InfoWriteCsv;
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
	$repoCsvNames = HttpClient::getUrlAsString( $repoCsvUrl );
	$repoNames = Cleaner::repoNamesArray( $repoCsvNames );
} catch ( \Exception $e ) {
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
} catch ( \Exception $e ) {
	$logger->log( 'Failed opening CSV file: ' . $e->getMessage() )->save();
	exit;
}

$orgCsvs = array_flip( Cleaner::orgsFromRepos( $repoNames ) );
foreach( $orgCsvs as $orgName => $value ) {
	$orgCsvs[$orgName] = new StatsWriteCsv( $orgName );
}

$gh = new GitHub( getenv('GITHUB_READ_TOKEN') );
foreach ( $repoNames as $repoName ) {

	try {
		$repoStatCsv = new StatsWriteCsv( Cleaner::repoFileName( $repoName ) );
	} catch ( \Exception $e ) {
		$logger->log( sprintf( 'Failed opening stats CSV file for %s: %s', $repoName, $e->getMessage() ) );
		$repoStatCsv = null;
	}

	///
	// Repo data
	//
	try {
		$repoData = [ 'Repo' => $gh->getRepo( $repoName ) ];
	} catch ( \Exception $e ) {
		$logger->log( sprintf( 'Failed getting Repo data for %s: %s', $repoName, $e->getMessage() ) );
		$repoData = [ 'Repo' => [] ];
	}

	///
	// Latest release data
	//
	try {
		$repoData['LatestRelease'] = $gh->getLatestRelease( $repoName );
	} catch ( \Exception $e ) {
		$logger->log( sprintf( 'Failed getting LatestRelease data for %s: %s', $repoName, $e->getMessage() ) );
		$repoData['LatestRelease'] = [];
	}

	$repoIsPrivate = isset( $repoData['Repo']['private'] ) && $repoData['Repo']['private'];
	$repoCanPush   = isset( $repoData['Repo']['permissions'] ) && $repoData['Repo']['permissions']['push'];

	if ( ! $repoIsPrivate ) {

		///
		// Community data
		//
		try {
			$repoData['Community'] = $gh->getCommunity( $repoName );
		} catch ( \Exception $e ) {
			$logger->log( sprintf( 'Failed getting Community data for %s: %s', $repoName, $e->getMessage() ) );
			$repoData['Community'] = [];
		}

		///
		// CI information
		//
		$repoData['CI'] = '';
		$circleCiConfig = 'https://github.com/' . $repoName . '/tree/master/.circleci/config.yml';
		$travisCiConfig = 'https://github.com/' . $repoName . '/tree/master/.travis.yml';
		if ( HttpClient::fileExists( $circleCiConfig ) ) {
			$repoData['CI'] = 'Circle';
		} elseif ( HttpClient::fileExists( $travisCiConfig ) ) {
			$repoData['CI'] = 'Travis';
		}
	}

	if ( ! $repoIsPrivate && $repoCanPush ) {

		///
		// Traffic data
		//
		foreach ( ['TrafficClones', 'TrafficRefs', 'TrafficViews'] as $dataType ) {
			try {
				$methodName          = 'get' . $dataType;
				$repoData[$dataType] = $gh->$methodName( $repoName );
			} catch ( \Exception $e ) {
				$logger->log( sprintf( 'Failed getting %s data for %s: %s', $dataType, $repoName, $e->getMessage() ) );
				$repoData[$dataType] = [];
			}
		}
	}

	///
	// CI file
	//


	///
	// Repo info data
	//
	try {
		$repoInfoCsv = new InfoWriteCsv( Cleaner::repoFileName( $repoName ) );
		foreach ( InfoWriteCsv::ELEMENTS as $index => $statName ) {
			$statParts = explode( SEPARATOR, $statName );
			$dataObject = $repoData[$statParts[0]];

			if ( empty( $dataObject[$statParts[1]] ) ) {
				continue;
			}

			if ( 'Repo' . SEPARATOR . 'topics' === $statName ) {
				$statValue = implode( ', ', $dataObject['topics'] );
				$statValue = Cleaner::text( $statValue );
				$repoInfoCsv->addData( [ $statName, $statValue ] );
				continue;
			}

			if ( 'Repo' . SEPARATOR . 'license' . SEPARATOR . 'spdx_id' === $statName ) {
				$licenseData = $dataObject['license'];
				$statValue = isset( $licenseData['spdx_id'] ) ? $licenseData['spdx_id'] : $licenseData['name'];
				$statValue = Cleaner::text( $statValue );
				$repoInfoCsv->addData( [ $statName, $statValue ] );
				continue;
			}

			if ( 'Repo' . SEPARATOR . 'size' === $statName ) {
				$statValue = Cleaner::absint( $dataObject['size'] );
				$repoInfoCsv->addData( [ $statName, $statValue ] );
				continue;
			}

			if ( 'Community' . SEPARATOR . 'health_percentage' === $statName ) {
				$statValue = Cleaner::absint( $dataObject['health_percentage'] );
				$repoInfoCsv->addData( [ $statName, $statValue ] );
				continue;
			}

			$statValue = Cleaner::text( $dataObject[$statParts[1]] );
			$repoInfoCsv->addData( [ $statName, $statValue ] );
		}
	} catch ( \Exception $e ) {
		$logger->log( sprintf( 'Failed opening info CSV file for %s: %s', $repoName, $e->getMessage() ) );
	}

	///
	// Combined stats data
	//
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

	$jsonFile = new RawJson( Cleaner::repoFileName( $repoName ) );
	$jsonFile->save( $repoData );

	try {
		$repoStatCsv->putClose();
	} catch ( \Exception $e ) {
		$logger->log( sprintf( 'Failed saving repo stats CSV for %s: %s', $repoName, $e->getMessage() ) );
	}

	try {
		$repoInfoCsv->putClose();
	} catch ( \Exception $e ) {
		$logger->log( sprintf( 'Failed saving repo info CSV for %s: %s', $repoName, $e->getMessage() ) );
	}
}

foreach ( $orgCsvs as $orgName => $orgCsv ) {
	try {
		$orgCsv->putClose();
	} catch ( \Exception $e ) {
		$logger->log( sprintf( 'Failed saving org stats CSV for %s: %s', $orgName, $e->getMessage() ) );
	}
}

try {
	$globalStatCsv->putClose();
} catch ( \Exception $e ) {
	$logger->log( 'Failed saving global stats CSV: ' . $e->getMessage() );
}

try {
	$referrerCsv->putClose();
} catch ( \Exception $e ) {
	$logger->log( 'Failed saving referrer stats: ' . $e->getMessage() );
}


////
///
// FIN!
//
$logger->save();
exit;
