<?php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/_bootstrap.php';

use DxSdk\Data\Cleaner;
use DxSdk\Data\Logger;

use DxSdk\Data\Api\HttpClient;
use DxSdk\Data\Api\GitHub;
use DxSdk\Data\Api\CodeCov;

use DxSdk\Data\Files\WriteStatsCsv;
use DxSdk\Data\Files\WriteInfoCsv;
use DxSdk\Data\Files\WriteReferrerCsv;

define( 'COMMAND_NAME', str_replace( [__DIR__.'/', '.php'], '', __FILE__ ) );

$logger = new Logger(COMMAND_NAME);

////
///
// Get all repos from the matrix spreadsheet.
// All repos are stored in the "All Repos" sheet and updated with every sheet update.
// That sheet is published as CSV from File > Publish to Web
//
$repoCsvUrl = getRepoCsv();
try {
	$repoCsvNames = file_exists($repoCsvUrl) ?
		file_get_contents($repoCsvUrl) :
		HttpClient::getUrlAsString($repoCsvUrl);
	$repoNames = Cleaner::repoNamesArray( $repoCsvNames );
} catch ( \Exception $e ) {
	$logger->log( 'Failed getting repos from CSV: ' . $e->getMessage() )->save();
	exit;
}

////
///
// Iterate through all repo names and get GitHub data.
// This will be decoded to use in memory, then combined and stored as the raw JSON.
//

$globalStatCsv = new WriteStatsCsv( 'stats' . SEPARATOR . 'global' );
$referrerCsv = new WriteReferrerCsv();

// Create org-level data storage.
$orgNames = Cleaner::orgsFromRepos( $repoNames );
$orgStatsCsvs = $orgInfoCsvs = array_flip( $orgNames );
foreach( $orgNames as $orgName ) {
	$orgStatsCsvs[$orgName] = new WriteStatsCsv( 'stats' . SEPARATOR . $orgName );
	$orgInfoCsvs[$orgName]  = new WriteInfoCsv( $orgName );
}

foreach ( $repoNames as $repoName ) {
    $cc = new CodeCov( $repoName, getenv('CODECOV_READ_TOKEN'), $logger );
    $gh = new GitHub( $repoName, getenv('GITHUB_READ_TOKEN'), $logger );

	$orgName = Cleaner::orgName( $repoName );
	$repoFileName = Cleaner::repoFileName( $repoName );

	$repoData = [
		'Repo' => $gh->getRepo(),
		'LatestRelease' => $gh->getLatestRelease(),
		'PullRequests' => $gh->getPullRequests(),
        'Issues' => ['count' => 0],
    ];

    if (isset( $repoData['Repo']['open_issues_count'] )) {
        $repoData['Issues']['count'] = $repoData['Repo']['open_issues_count'] - $repoData['PullRequests']['count'];
    }

	$repoIsPrivate = isset( $repoData['Repo']['private'] ) && $repoData['Repo']['private'];
	$repoCanPush   = isset( $repoData['Repo']['permissions'] ) && $repoData['Repo']['permissions']['push'];

	// No community data if repo is private.
	if ( ! $repoIsPrivate ) {
		$repoData['Community'] = $gh->getCommunity();
		$repoData['CI'] = $gh->getCiType();
		$repoData['CodeCov'] = $cc->getCoverage();
	}

	// No traffic data if GitHub token cannot push; no traffic counted if private.
	if ( ! $repoIsPrivate && $repoCanPush ) {
		$repoData['TrafficClones'] = $gh->getTrafficClones();
		$repoData['TrafficViews'] = $gh->getTrafficViews();
	}

	///
	// Org-level info data
	//
	$orgInfoCsvs[$orgName]->addData( $repoData );

	///
	// Combined stats data
	//
	$repoStatCsv = new WriteStatsCsv( $repoFileName );
	foreach ( WriteStatsCsv::ELEMENTS as $index => $stat ) {
		list( $dataObject, $property ) = explode( SEPARATOR, $stat );

		// Make sure we have a value to add.
		$statValue = 0;
		if ( isset( $repoData[$dataObject][$property] ) ) {
			$statValue = Cleaner::absint( $repoData[$dataObject][$property] );
		}

		$addData = [ $index, $statValue ];
		$globalStatCsv->addData( $addData );
		$orgStatsCsvs[$orgName]->addData( $addData );

		if ( $repoStatCsv instanceof WriteStatsCsv ) {
			$repoStatCsv->addData( $addData );
		}
	}
	$repoStatCsv->putClose();
}

foreach( $orgNames as $orgName ) {
	$orgStatsCsvs[$orgName]->putClose();
	$orgInfoCsvs[$orgName]->close();
}

$globalStatCsv->putClose();
$referrerCsv->putClose();


////
///
// FIN!
//
$logger->save();
exit;
