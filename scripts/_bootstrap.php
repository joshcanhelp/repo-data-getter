<?php
set_time_limit(0);
date_default_timezone_set ( 'Europe/London' );

use Dotenv\Dotenv;

try {
  $dotenv = Dotenv::create(__DIR__.'/..');
  $dotenv->load();
} catch (Exception $e) {
  echo "❌ {$e->getMessage()}\n";
  exit;
}

$outputDir = getenv('OUTPUT_DIR');
if (!$outputDir) {
    echo "❌ Missing OUTPUT_DIR env value\n";
    exit;
}

if (!file_exists($outputDir)) {
    echo "❌ Directory {$outputDir} not found\n";
    exit;
}

if ('/' !== $outputDir[strlen($outputDir)-1]) {
  $outputDir .= '/';
}

$subDirs = [
  'csv',
  'logs',
  'json',
  'json/repos',
  'json/repos-pulls',
  'json/repos-community-profile',
  'json/repos-traffic-clones',
  'json/repos-traffic-views',
  'json/repos-releases-latest',
  'json/gh',
];

foreach($subDirs as $dir) {
  if (!file_exists($outputDir . $dir) && !mkdir($outputDir . $dir)) {
      echo "❌ Directory {$outputDir}{$dir} could not be created\n";
      exit;
  }
}

define( 'DATA_SAVE_PATH_SLASHED', $outputDir );
define( 'SEPARATOR', '--' );
define( 'DATE_NOW', date( 'Y-m-d_H-i-s' ) );
define( 'TIME_NOW', date( 'U' ) );

function getRepoCsv()
{
  $repoCsvUrl = getenv('REPO_CSV_URL');
  if (!$repoCsvUrl) {
      echo "❌ Missing REPO_CSV_URL env value\n";
      exit;
  }

  if (filter_var($repoCsvUrl, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED)) {
    return $repoCsvUrl;
  }

  if (!file_exists($repoCsvUrl)) {
      echo "❌ REPO_CSV_URL {$repoCsvUrl} not found\n";
      exit;
  }

  return $repoCsvUrl;
}
