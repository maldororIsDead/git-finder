#!/usr/bin/env php
<?php

use App\Commands\DownloadGit;
use Symfony\Component\Console\Application;
use App\Client;

use App\Services\Downloader;
use App\Services\Extraction;
use App\Services\FinderMethod;
use App\Services\Parser;

require __DIR__ . '/vendor/autoload.php';

const DOWNLOADS_PATH = 'storage';
const PUBLIC_METHOD = '/(?P<method>((a|s).*)?public([\w\s]*)?function\s[\w]+\([^)]*\).*)/';

$app = new Application;

$app->add(new DownloadGit(new Downloader(new Client, DOWNLOADS_PATH), new Extraction(DOWNLOADS_PATH),
    new FinderMethod(DOWNLOADS_PATH, PUBLIC_METHOD), new Parser(PUBLIC_METHOD)));

$app->run();