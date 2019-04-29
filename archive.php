<?php

$repo = json_decode( $thisRepoData['repo'], true );
$repoParsed = [
	// Information
	'private' => ! empty( $repo['private'] ),
	'html_url' => empty( $repo['html_url'] ) ? '' : Cleaner::url( $repo['html_url'] ),
	'description' => empty( $repo['description'] ) ? '' : Cleaner::text( $repo['description'] ),
	'topics' => empty( $repo['topics'] ) ? '' : implode( ', ', Cleaner::textArray( $repo['topics'] ) ),
	'language' => empty( $repo['language'] ) ? '' : Cleaner::text( $repo['language'] ),
	'license' => empty( $repo['license'] ) || empty( $repo['license']['spdx_id'] ) ?
		'Unknown' :
		Cleaner::text( $repo['license']['spdx_id'] ),

	// Stats
	'stargazers_count' => empty( $repo['stargazers_count'] ) ? 0 : Cleaner::absint( $repo['stargazers_count'] ),
	'subscribers_count' => empty( $repo['subscribers_count'] ) ? 0 : Cleaner::absint( $repo['subscribers_count'] ),
	'forks' => empty( $repo['forks'] ) ? 0 : Cleaner::absint( $repo['forks'] ),
	'open_issues_count' => empty( $repo['open_issues_count'] ) ? 0 : Cleaner::absint( $repo['open_issues_count'] ),
	'size' => empty( $repo['size'] ) ? 0 : Cleaner::absint( $repo['size'] ),

	// Dates
	'created_at' => empty( $repo['created_at'] ) ? '' : Cleaner::date( $repo['created_at'] ),
	'updated_at' => empty( $repo['updated_at'] ) ? '' : Cleaner::date( $repo['updated_at'] ),
	'pushed_at' => empty( $repo['pushed_at'] ) ? '' : Cleaner::date( $repo['pushed_at'] ),
];

// TODO: Remove debugging
echo "\n" . print_r( $repoParsed, TRUE ) . "\n";
