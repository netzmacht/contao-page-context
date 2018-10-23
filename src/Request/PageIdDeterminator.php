<?php

declare(strict_types=1);

namespace Netzmacht\Contao\PageContext\Request;

use Netzmacht\Contao\PageContext\Exception\DeterminePageIdFailed;
use Symfony\Component\HttpFoundation\Request;

/**
 * The page id determinator is responsible to determinate the page id for a given request.
 */
interface PageIdDeterminator
{
    /**
     * Check if the determinator is able to determinate page id from the request.
     *
     * @param Request $request The given request.
     *
     * @return bool
     */
    public function match(Request $request): bool;

    /**
     * Determinate the page id and return it as integer.
     *
     * @param Request $request The given request.
     *
     * @return int
     *
     * @throws DeterminePageIdFailed When determinator wasn't able to determinate the page id.
     */
    public function determinate(Request $request): int;
}
