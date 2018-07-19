<?php

namespace App\Services;

use App\Client;
use GuzzleHttp\ClientInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Downloader
{
    /** @var OutputInterface */
    protected $output;

    /** @var Client */
    protected $client;

    /** @var ProgressBarService */
    protected $progressBar;

    /** @var string */
    protected $path;

    public function __construct(ClientInterface $client, string $path)
    {
        $this->client = $client;
        $this->path = $path;
    }

    public function getClient() {
        return $this->client;
    }

    public function download(string $url, string $path): Downloader
    {
        if (!file_exists($this->path)) {
            mkdir($this->path);
        }

        $this->client->request('get', $url, [
            'save_to' => $path,
            'progress' => [$this->client, 'onProgress'],
        ]);

        return $this;
    }
}