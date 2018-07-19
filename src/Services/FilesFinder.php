<?php

namespace App\Services;

use Symfony\Component\Finder\Finder;

class FilesFinder
{
    /** @var string */
    protected $path;

    /** @var string */
    protected $regExPublicMethod;

    public function __construct(string $path, string $regExPublicMethod)
    {
        $this->path = $path;
        $this->regExPublicMethod = $regExPublicMethod;
    }

    public function finder(): Finder
    {
        return Finder::create()
            ->in($this->path)
            ->name('*.php')
            ->contains($this->regExPublicMethod);
    }
}