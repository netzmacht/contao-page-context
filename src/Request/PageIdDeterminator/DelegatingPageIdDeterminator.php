<?php

declare(strict_types=1);

namespace Netzmacht\Contao\PageContext\Request\PageIdDeterminator;

use Assert\Assertion;
use Netzmacht\Contao\PageContext\Exception\DeterminePageIdFailed;
use Netzmacht\Contao\PageContext\Request\PageIdDeterminator;
use Symfony\Component\HttpFoundation\Request;

final class DelegatingPageIdDeterminator implements PageIdDeterminator
{
    /** @param PageIdDeterminator[] $determinators Page id determinators. */
    public function __construct(private readonly array $determinators)
    {
        Assertion::allImplementsInterface($determinators, PageIdDeterminator::class);
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

    /**
     * {@inheritDoc}
     *
     * @throws DeterminePageIdFailed When page id could not be determined.
     */
    public function determinate(Request $request): int
    {
        foreach ($this->determinators as $determinator) {
            if ($determinator->match($request)) {
                return $determinator->determinate($request);
            }
        }

        throw new DeterminePageIdFailed('Could not determine page id for URI: ' . $request->getUri());
    }
}
