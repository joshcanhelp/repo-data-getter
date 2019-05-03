<?php
require 'vendor/autoload.php';

use DxSdk\Data\Api\HttpClient;
use DxSdk\Data\Files\ReferrerWriteCsv;
use DxSdk\Data\Cleaner;

define( 'DATA_SAVE_PATH_SLASHED', dirname(__FILE__) . '/test-data/' );

$referrers = new ReferrerWriteCsv();

$refArray = json_decode( '[
    {
        "referrer": "Google",
        "count": 211,
        "uniques": 151
    },
    {
        "referrer": "github.com",
        "count": 30,
        "uniques": 16
    },
    {
        "referrer": "auth0.com",
        "count": 20,
        "uniques": 13
    },
    {
        "referrer": "wordpress.org",
        "count": 17,
        "uniques": 5
    },
    {
        "referrer": "community.auth0.com",
        "count": 5,
        "uniques": 4
    },
    {
        "referrer": "DuckDuckGo",
        "count": 3,
        "uniques": 3
    },
    {
        "referrer": "accounts.google.com",
        "count": 2,
        "uniques": 2
    },
    {
        "referrer": "epix.net.pl",
        "count": 2,
        "uniques": 1
    },
    {
        "referrer": "europee2019.votoarcobaleno.it",
        "count": 2,
        "uniques": 1
    },
    {
        "referrer": "oshershalom.com",
        "count": 2,
        "uniques": 1
    }
]', true );
$referrers->addData( $refArray );

$refArray2 = json_decode( '[
    {
        "referrer": "Google",
        "count": 2080,
        "uniques": 1034
    },
    {
        "referrer": "github.com",
        "count": 576,
        "uniques": 232
    },
    {
        "referrer": "auth0.com",
        "count": 447,
        "uniques": 191
    },
    {
        "referrer": "npmjs.com",
        "count": 327,
        "uniques": 130
    },
    {
        "referrer": "community.auth0.com",
        "count": 73,
        "uniques": 40
    },
    {
        "referrer": "DuckDuckGo",
        "count": 42,
        "uniques": 23
    },
    {
        "referrer": "issues.sonatype.org",
        "count": 27,
        "uniques": 7
    },
    {
        "referrer": "accounts.google.com",
        "count": 23,
        "uniques": 11
    },
    {
        "referrer": "Bing",
        "count": 10,
        "uniques": 5
    },
    {
        "referrer": "daniel-starling.com",
        "count": 5,
        "uniques": 2
    }
]', true );
$referrers->addData( $refArray2 );

//
///
////
echo '<pre>' . print_r( $referrers->putClose(), TRUE ) . '</pre>';
////
///
//
