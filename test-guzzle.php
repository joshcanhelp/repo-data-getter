<?php
require 'vendor/autoload.php';

use DxSdk\Data\Api\HttpClient;

echo '<pre>' . print_r( HttpClient::justGet( 'https://www.joshcanhelp.com/asdasdasds' ), TRUE ) . '</pre>'; die();

