<?php

namespace App\Services;

use ZipArchive;

class Extraction
{
    /** @var string */
    protected $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function extract(string $zipFile): void
    {
        $archive = new ZipArchive;

        $archive->open($zipFile);
        $archive->extractTo($this->path);
        $archive->close();

        unlink($zipFile);
    }
}