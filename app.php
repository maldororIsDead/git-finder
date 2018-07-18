#!/usr/bin/env php
<?php

use App\Commands\DownloadGitRepository;
use Symfony\Component\Console\Application;

require __DIR__ . '/vendor/autoload.php';

$app = new Application;

$app->add(new DownloadGitRepository);

$app->run();