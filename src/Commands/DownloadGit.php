<?php

namespace App\Commands;

use App\Client;
use App\Services\Downloader;
use App\Services\Extracter;
use App\Services\FilesFinder;
use App\Services\Parser;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class DownloadGit extends Command
{
    const DOWNLOADS_PATH = 'storage';

    /** @var OutputInterface */
    protected $output;

    /** @var Downloader */
    protected $downloader;

    /** @var Extracter */
    protected $extraction;

    /** @var FilesFinder */
    protected $finder;

    /** @var Client */
    protected $client;

    /** @var Parser */
    protected $parser;

    public function __construct(Downloader $downloader, Extracter $extraction, FilesFinder $finder, Parser $parser)
    {
        parent::__construct();

        $this->downloader = $downloader;
        $this->extraction = $extraction;
        $this->finder = $finder;
        $this->parser = $parser;
    }

    public function configure(): void
    {
        $this->setName('download-git')
            ->setDescription('Downloads a GIT Repository')
            ->addArgument('urls', InputArgument::IS_ARRAY, 'Urls to download \'user/repository\' ');
    }

    public function execute(InputInterface $input, OutputInterface $output): void
    {
        $this->output = $output;

        $client = $this->downloader->getClient();
        $client->setOutput($this->output);

        $urls = $input->getArgument('urls');

        foreach ($urls as $url) {
            $gitUrl = $this->createGitUrl($url);
            $path = $this->getPath($url);
            $this->downloader->download($gitUrl, $path);
            $this->extraction->extract($path);
        }

        $this->output->writeln('');

        $files = $this->finder->finder();

        $this->outputParsedData($this->parser->parseFiles($files, $output));
    }

    protected function createGitUrl(string $url): string
    {
        return "https://github.com/{$url}/archive/master.zip";
    }

    protected function getPath(string $filename): string
    {
        return static::DOWNLOADS_PATH . DIRECTORY_SEPARATOR . str_replace('/', '-', $filename);
    }

    protected function outputParsedData(array $parsedFiles): void
    {
        foreach ($parsedFiles as $file => $methods) {
            $this->output->writeln('<info>' . $file . '</info>');

            foreach ($methods as $method) {
                $this->output->writeln('<comment>' . PHP_EOL . "\t" . $method . '<comment>' . PHP_EOL);
            }
        }
    }
}