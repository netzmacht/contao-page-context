<?php

declare(strict_types=1);

namespace Netzmacht\Contao\PageContext\Request\PageIdDeterminator;

use Netzmacht\Contao\PageContext\Exception\DeterminePageIdFailed;
use Netzmacht\Contao\PageContext\Request\PageIdDeterminator;
use Symfony\Component\HttpFoundation\Request;

final class DelegatingPageIdDeterminator implements PageIdDeterminator
{
    /**
     * @var PageIdDeterminator[]
     */
    private $determinators;

    /**
     * DelegatingPageIdDeterminator constructor.
     *
     * @param PageIdDeterminator[] $determinators
     */
    public function __construct(array $determinators)
    {
        $this->determinators = $determinators;
    }

    public function match(Request $request): bool
    {
        foreach ($this->determinators as $determinator) {
            if ($determinator->match($request)) {
                return true;
            }
        }

        return false;
    }

    public function determinate(Request $request): int
    {
        foreach ($this->determinators as $determinator) {
            if ($determinator->match($request)) {
                return $determinator->determinate($request);
            }
        }

        throw new DeterminePageIdFailed('Could not determine uri for URI: ' . $request->getUri());
    }
}
