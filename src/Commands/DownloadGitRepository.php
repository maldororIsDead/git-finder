<?php

namespace App\Commands;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use ZipArchive;

class DownloadGitRepository extends Command
{
    const DOWNLOADS_PATH = 'storage';
    const PUBLIC_METHOD = '/(?P<method>((a|s).*)?public([\w\s]*)?function\s[\w]+\([^)]*\).*)/';

    /** @var ProgressBar */
    protected $progressBar;

    /** @var OutputInterface */
    protected $output;

    public function configure(): void
    {
        $this->setName('download-git')
            ->setDescription('Downloads a GIT Repository')
            ->addArgument('urls', InputArgument::IS_ARRAY, 'Urls to download \'user/repository\' ');
    }

    public function execute(InputInterface $input, OutputInterface $output): void
    {
        $this->output = $output;

        $urls = $input->getArgument('urls');

        foreach ($urls as $url) {
            $gitUrl = $this->createGitUrl($url);
            $path = $this->getPath($url);

            $this->download($gitUrl, $path)
                ->extract($path);
        }

        $files = $this->finder();
        $this->parseFiles($files);
    }

    protected function createGitUrl(string $url): string
    {
        return 'https://github.com/' . $url . '/archive/master.zip';
    }

    public function download(string $url, string $path): DownloadGitRepository
    {
        if (!file_exists(static::DOWNLOADS_PATH)) {
            mkdir(static::DOWNLOADS_PATH);
        }

        $this->getClient()->request('get', $url, [
            'save_to' => $path,
            'progress' => [$this, 'onProgress'],
        ]);

        $this->output->writeln(PHP_EOL);

        return $this;
    }

    public function finder(): Finder
    {
        return $files = Finder::create()
            ->in(static::DOWNLOADS_PATH)
            ->name('*.php')
            ->contains(static::PUBLIC_METHOD)
            ->notContains('class=');
    }

    public function extract(string $zipFile): void
    {
        $archive = new ZipArchive;
        $archive->open($zipFile);
        $archive->extractTo(static::DOWNLOADS_PATH);
        $archive->close();

        unlink($zipFile);
    }

    protected function getPath(string $filename): string
    {
        return static::DOWNLOADS_PATH . DIRECTORY_SEPARATOR . str_replace('/', '-', $filename);
    }

    public function getClient(): ClientInterface
    {
        return new Client;
    }

    protected function parseFiles(Finder $files): void
    {
        foreach ($files as $file) {
            $this->output->writeln('<info>' . $file->getRelativePathname() . '</info>');

            foreach ($this->parsePublicMethods($file->getContents()) as $method) {
                $this->output->writeln('<comment>' . PHP_EOL . "\t" . $method . '<comment>' . PHP_EOL);
            }
        }
    }

    protected function parsePublicMethods(string $content): array
    {
        preg_match_all(static::PUBLIC_METHOD, $content, $matches);

        return str_replace(['public', 'function'], '', $matches['method']);
    }

    public function onProgress(int $total, int $downloaded): void
    {
        if ($total <= 0) {
            return;
        }

        if (!$this->progressBar) {
            $this->progressBar = $this->createProgressBar(100);
        }

        $this->progressBar->setProgress(100 / $total * $downloaded);
    }

    public function createProgressBar(int $max): ProgressBar
    {
        $bar = new ProgressBar($this->output, $max);
        $bar->setBarCharacter('<fg=green>·</>');
        $bar->setEmptyBarCharacter('<fg=red>·</>');
        $bar->setProgressCharacter('<fg=green>ᗧ</>');
        $bar->setFormat("%current:8s%/%max:-8s% %bar% %percent:5s%% %elapsed:7s%/%estimated:-7s% %memory%");

        return $bar;
    }
}