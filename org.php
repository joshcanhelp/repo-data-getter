<?php
set_time_limit(0);
date_default_timezone_set ( 'Europe/London' );

require 'vendor/autoload.php';

use Dotenv\Dotenv;
use DxSdk\Data\Api\GitHub;
use DxSdk\Data\Files\WriteOrgCsv;
use DxSdk\Data\Logger;

$dotenv = Dotenv::create(__DIR__);
$dotenv->load();

// Date/time to use in file names.
define( 'DATA_SAVE_PATH_SLASHED', dirname(__FILE__) . '/data/' );
define( 'SEPARATOR', '--' );
define( 'DATE_NOW', date( 'Y-m-d_H-i-s' ) );
define( 'TIME_NOW', date( 'U' ) );

$logger = new Logger();

$org_name = $argv[1] ?? null;
if (!$org_name) {
    echo "❌ Missing org name\n";
    exit;
}

echo "✅ Getting repos for {$org_name}\n";

$gh = new GitHub( getenv('GITHUB_READ_TOKEN'), $logger );

$org_repos = $gh->getOrgRepos($org_name);
if (!$org_repos) {
    echo "❌ No org repos found\n";
    echo $logger->out();
    exit;
}

$org_repos_count = count($org_repos);
echo "✅ Processing {$org_repos_count} public repos for {$org_name}\n";

$orgCsv = new WriteOrgCsv( $org_name );

foreach ( $org_repos as $repo ) {
	$orgCsv->addData( $repo );
}

$orgCsv->close();
echo $logger->out();
exit;
