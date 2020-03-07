<?php
require 'vendor/autoload.php';

use DxSdk\Data\Api\HttpClient;

echo (int) HttpClient::fileExists( 'https://www.joshcanhelp.com/resume' );

