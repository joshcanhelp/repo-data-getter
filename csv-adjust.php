<?php
require 'vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::create(__DIR__);
$dotenv->load();

// Date/time to use in file names.
define( 'DATA_SAVE_PATH_SLASHED', dirname(__FILE__) . '/data/' );
define( 'SEPARATOR', '--' );

$csvDir = DATA_SAVE_PATH_SLASHED . 'csv/';
$allCsvFiles = scandir($csvDir, SCANDIR_SORT_DESCENDING);

foreach( $allCsvFiles as $fileName ) {
    if (0 === strpos($fileName, '.') || 0 === strpos($fileName, 'info--') ) {
        continue;
    }

    echo "Processing {$fileName} ...\n";

    $handle = fopen($csvDir . $fileName, 'r');
    $csvArray = [];
    while ( $csvLine = fgetcsv($handle) ) {
        $csvArray[] = $csvLine;
    }
    fclose($handle);

    $handle = fopen($csvDir . $fileName, 'w');
    foreach($csvArray as $index => $row) {
        if (0 === $index) {
            $row[3] = 'PullRequests' . SEPARATOR . 'count';
            $row[5] = 'Issues' . SEPARATOR . 'count';
            fputcsv($handle, $row);
            continue;
        }

        $row[3] = 0;
        fputcsv($handle, $row);
    }
}
