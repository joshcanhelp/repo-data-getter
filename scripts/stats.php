<?php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/_bootstrap.php';

define( 'COMMAND_NAME', str_replace( [__DIR__.'/', '.php'], '', __FILE__ ) );

use DxSdk\Data\Api\HttpClient;
use DxSdk\Data\Api\GitHub;
use DxSdk\Data\Api\CodeCov;
use DxSdk\Data\Cleaner;
use DxSdk\Data\Files\WriteStatsCsv;
use DxSdk\Data\Files\WriteInfoCsv;
use DxSdk\Data\Logger;

$logger = new Logger();

$orgName = $argv[1] ?? null;
if (!$orgName) {
    $logger->log( 'Missing org name' )->save();
    exit;
}

$topic = $argv[2] ?? null;
if (!$topic) {
    $logger->log( 'Missing topic name' )->save();
    exit;
}

try {
    $searchResults = HttpClient::getUrlAsString(
        GitHub::API_BASE_URL . 'search/repositories?per_page=100&q=org:' . $orgName . '+topic:' . $topic . '+is:public',
        [
            'Accept' => GitHub::HEADER_TOPICS,
            'Authorization' => 'token ' . getenv('GITHUB_READ_TOKEN'),
        ]
    );
} catch ( \Exception $e ) {
    $logger->log( 'Failed getting repos for ' . $orgName . ': ' . $e->getMessage() )->save();
    exit;
}

$searchData = json_decode($searchResults, true);
if (empty($searchData['total_count'])) {
    $logger->log( 'No repos found for '. $orgName )->save();
    exit;
}

$orgStatsCsv = new WriteStatsCsv( 'stats' . SEPARATOR . $orgName );
$orgInfoCsv  = new WriteInfoCsv( $orgName );

foreach ( $searchData['items'] as $repo ) {
    $repoName = $repo['full_name'];
    $repoFileName = Cleaner::repoFileName( $repoName );

    $cc = new CodeCov( $repoName, getenv('CODECOV_READ_TOKEN'), $logger );
    $gh = new GitHub( $repoName, getenv('GITHUB_READ_TOKEN'), $logger );

    $repoData = [
        'Repo' => $repo,
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

    // Org-level info data
    $orgInfoCsv->addData( $repoData );

    // Combined stats data
    $repoStatCsv = new WriteStatsCsv( $repoFileName );
    foreach ( WriteStatsCsv::ELEMENTS as $index => $stat ) {
        list( $dataObject, $property ) = explode( SEPARATOR, $stat );

        // Make sure we have a value to add.
        $statValue = 0;
        if ( isset( $repoData[$dataObject][$property] ) ) {
            $statValue = Cleaner::absint( $repoData[$dataObject][$property] );
        }

        $addData = [ $index, $statValue ];
        $orgStatsCsv->addData( $addData );
        $repoStatCsv->addData( $addData );
    }
    $repoStatCsv->putClose();
}

$orgStatsCsv->putClose();
$orgInfoCsv->close();

$logger->save();
exit;
