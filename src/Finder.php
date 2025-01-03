<?php

declare(strict_types=1);

namespace Onepix\WordPressHooksStubGenerator;

use Symfony\Component\Finder\Finder as SymfonyFinder;

class Finder extends SymfonyFinder
{
    public function __construct()
    {
        parent::__construct();
    }

    public function init_default(): void
    {
        $this
            ->files()
            ->name('*.php')
            ->exclude('vendor');
    }
}
