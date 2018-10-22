<?php

declare(strict_types=1);

namespace Netzmacht\Contao\PageContext\Request;

use Symfony\Component\HttpFoundation\Request;

interface PageIdDeterminator
{
    public function supports(Request $request): bool;

    public function determinate(Request $request): int;
}
