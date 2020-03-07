<?php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/_bootstrap.php';

define( 'COMMAND_NAME', str_replace( [__DIR__.'/', '.php'], '', __FILE__ ) );

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
            $row[] = 'PullRequests' . SEPARATOR . 'count';
            $row[] = 'Issues' . SEPARATOR . 'count';

            fputcsv($handle, $row);
            continue;
        }

        if (!isset($row[3])||!isset($row[5])) {
            echo "Empty row on {$fileName} ...\n";
            continue;
        }

        $row[] = 0;
        $row[] = 0;
        fputcsv($handle, $row);
    }
}
