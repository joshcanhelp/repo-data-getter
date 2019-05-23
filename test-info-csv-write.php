<?php
require 'vendor/autoload.php';

use DxSdk\Data\Files\WriteInfoCsv;

// Date/time to use in file names.
define( 'DATA_SAVE_PATH_SLASHED', dirname(__FILE__) . '/data/' );
define( 'SEPARATOR', '--' );
define( 'DATE_NOW', date( 'Y-m-d_H-i-s' ) );
define( 'TIME_NOW', date( 'U' ) );

$infoWriteCsv = new WriteInfoCsv('banana');
$infoWriteCsv->addData( [
	'Repo' => [
		'name' => uniqid(),
		'description' => uniqid(),
		'homepage' => 'https://' . uniqid() . '.com',
		'topics' => [ uniqid(), uniqid(), uniqid() ],
		'license' => [
			'spdx_id' => uniqid(),
		],
		'language' => uniqid(),
		'size' => mt_rand( 1000, 9999 ),
		'pushed_at' => uniqid() . 'T' . uniqid(),
		'created_at' => uniqid() . 'T' . uniqid(),
		'private' => false,
		'html_url' => 'https://github.com/' . uniqid() . '/' . uniqid(),
	],
	'Community' => [
		'health_percentage' => mt_rand( 1, 100 ),
	],
	'LatestRelease' => [
		'name' => uniqid(),
		'published_at' => uniqid() . 'T' . uniqid(),
	],
	'CI' => uniqid()
] );
$infoWriteCsv->putClose();
//
///
////
//echo '<pre>' . print_r( $infoWriteCsv->out(), TRUE ) . '</pre>';
////
///
//
