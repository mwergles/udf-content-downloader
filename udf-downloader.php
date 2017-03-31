#!/usr/bin/env php
<?php

use Symfony\Component\Console\Application;
use \Downloader\Command\DownloadCommand;
use \Downloader\Service\DownloadService;
use GuzzleHttp\Client;

require_once __DIR__ . '/vendor/autoload.php';

define('DS', DIRECTORY_SEPARATOR);
define('ROOT', __DIR__);

$application = new Application('UDF Content Downloader', '0.0.1');

$application->add(new DownloadCommand(new DownloadService(new Client())));

$application->run();