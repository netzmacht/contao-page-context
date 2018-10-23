<?php

/**
 * Contao Page Context
 *
 * @package    contao-page-context
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  2018 netzmacht David Molineus.
 * @license    LGPL-3.0 https://github.com/netzmacht/contao-page-context/blob/master/LICENSE
 * @filesource
 */

declare(strict_types=1);

namespace Netzmacht\Contao\PageContext\Request\PageIdDeterminator;

use Assert\Assertion;
use Netzmacht\Contao\PageContext\Exception\DeterminePageIdFailed;
use Netzmacht\Contao\PageContext\Request\PageIdDeterminator;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class DelegatingPageIdDeterminator
 */
final class DelegatingPageIdDeterminator implements PageIdDeterminator
{
    /**
     * Page id determinators.
     *
     * @var PageIdDeterminator[]
     */
    private $determinators;

    /**
     * DelegatingPageIdDeterminator constructor.
     *
     * @param PageIdDeterminator[] $determinators Page id determinators.
     */
    public function __construct(array $determinators)
    {
        Assertion::allImplementsInterface($determinators, PageIdDeterminator::class);

        $this->determinators = $determinators;
    }

    /**
     * {@inheritdoc}
     */
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
     * {@inheritdoc}
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
