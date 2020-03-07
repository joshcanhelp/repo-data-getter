# GitHub Repo Stats

This library collects statistics and information from GitHub repos. The scripts here are meant to be run on a regular cron job and will store each run in a CSV file.

## Requirements

- PHP 7.1 or later in the terminal
- Composer

## Getting Started

Make sure you have the right PHP version installed in the terminal:

```bash
$ php -v
# PHP 7.1.33 (cli)
```

Install dependencies:

```bash
$ composer install
```

Set up your `.env` with a [GitHub token](https://help.github.com/en/github/authenticating-to-github/creating-a-personal-access-token-for-the-command-line), [CodeCov token](https://docs.codecov.io/reference#authorization), and URL or file path to the CSV containing the repos to get:

```text
GITHUB_READ_TOKEN="GitHub token with read access"
CODECOV_READ_TOKEN="CodeCov token with read access"
REPO_CSV_URL="URL to repo CSV"
OUTPUT_CSV_DIR="Path to local output directory"
```

The GitHub token user will need to have push access to get traffic data ([more information](https://help.github.com/en/github/visualizing-repository-data-with-graphs/viewing-traffic-to-a-repository)). This token is not technically required but is necessary if you're pulling many repos, need access to private/internal repos, or want to include traffic data.

The repo CSV should be just a simple 1-column list of repo names, including the organization. An example could be:

```csv
joshcanhelp/wordpress-to-markdown
joshcanhelp/wordpress-to-11ty
joshcanhelp/instaday
```

You can also use a Google Sheet here by going to **File > Publish to the web > Link > CSV** and adding that link. Make sure the the CSV being published is only a single column of valid repos. Any value without `"/"` will be skipped.

The `OUTPUT_CSV_DIR` should be a path to an existing directly where the generated files will be saved.

## Running scripts

Once the above is complete, you can run the `run.php` to generate the stats and information CSVs:

```bash
$ php scripts/run.php
```

You can also run the `org.php` with an org name after to get all public repos:

```bash
$ php scripts/org.php ObstacleParty
# ✅ Getting repos for ObstacleParty
# ✅ Processing 2 public repos for ObstacleParty
```
