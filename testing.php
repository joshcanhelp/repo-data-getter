<?php
set_time_limit(0);

require 'vendor/autoload.php';

$elements = [
	'Repo|stargazers_count', 'Repo|subscribers_count', 'Repo|forks', 'Repo|open_issues_count',
	'TrafficClones|count', 'TrafficClones|uniques',
	'TrafficViews|count', 'TrafficViews|uniques',
];
$elements = array_flip( $elements );
$data = array_map( function () { return 0; }, $elements );
// TODO: Remove debugging
echo '<pre>' . print_r( $data, TRUE ) . '</pre>';
