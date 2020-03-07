<?php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/_bootstrap.php';

define( 'COMMAND_NAME', str_replace( [__DIR__.'/', '.php'], '', __FILE__ ) );

use DxSdk\Data\Api\GitHub;
use DxSdk\Data\Files\WriteOrgCsv;
use DxSdk\Data\Logger;

$logger = new Logger();

$org_name = $argv[1] ?? null;
if (!$org_name) {
    echo "❌ Missing org name\n";
    exit;
}

echo "✅ Getting repos for {$org_name}\n";

$gh = new GitHub( $org_name, getenv('GITHUB_READ_TOKEN'), $logger );

$org_repos = $gh->getOrgRepos();
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
