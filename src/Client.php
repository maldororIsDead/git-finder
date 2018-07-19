<?php

namespace App;

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;


class Client extends \GuzzleHttp\Client
{
    /** @var OutputInterface */
    protected $output;

    /** @var ProgressBar */
    protected $progressBar;

    public function setOutput(OutputInterface $output): void
    {
        $this->output = $output;
    }

    public function getOutput(): OutputInterface
    {
        return $this->output;
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