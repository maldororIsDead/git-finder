<?php

namespace App\Services;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Console\Output\OutputInterface;

class Parser
{
    /** @var string */
    protected $regExpression;

    /** @var Finder */
    protected $files;

    /** @var OutputInterface */
    protected $output;

    public function __construct(string $regExpression)
    {
        $this->regExpression = $regExpression;
    }

    public function parseFiles(Finder $files): array
    {
        $parsedFiles = [];

        $this->files = $files;
 
        foreach ($this->files as $file) {
            $parsedFiles[$file->getRelativePathname()] = $this->parsePublicMethods($file->getContents());
        }

        return $parsedFiles;
    }

    protected function parsePublicMethods(string $content): array
    {
        preg_match_all($this->regExpression, $content, $matches);

        return str_replace(['public', 'function'], '', $matches['method']);
    }
}