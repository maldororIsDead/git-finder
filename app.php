#!/usr/bin/env php
<?php

use App\Commands\DownloadGit;
use Symfony\Component\Console\Application;
use App\Client;

use App\Services\Downloader;
use App\Services\Extracter;
use App\Services\FilesFinder;
use App\Services\Parser;

require __DIR__ . '/vendor/autoload.php';

const DOWNLOADS_PATH = 'storage';
const PUBLIC_METHOD = '/(?P<method>((a|s).*)?public([\w\s]*)?function\s[\w]+\([^)]*\).*)/';

$downloader = new Downloader(new Client, DOWNLOADS_PATH);

$extracter = new Extracter(DOWNLOADS_PATH);

$parser = new Parser(PUBLIC_METHOD);

$finder = new FilesFinder(DOWNLOADS_PATH, PUBLIC_METHOD);

$app = new Application;

$app->add(new DownloadGit($downloader, $extracter, $finder, $parser));

$app->run();