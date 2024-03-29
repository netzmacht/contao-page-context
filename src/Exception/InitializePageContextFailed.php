<?php

declare(strict_types=1);

namespace Netzmacht\Contao\PageContext\Exception;

use RuntimeException;

use function sprintf;

final class InitializePageContextFailed extends RuntimeException
{
    /**
     * Create exception with error message for failed page id determination.
     *
     * @return InitializePageContextFailed
     */
    public static function determinatePageIdFailed(): self
    {
        return new self('Determinating page id failed.');
    }

    /**
     * Create exception with error message for failing to load given page.
     *
     * @param int $pageId The page id.
     *
     * @return InitializePageContextFailed
     */
    public static function pageNotFound(int $pageId): self
    {
        return new self(sprintf('Could not fetch model from determinated page id "%s"', $pageId));
    }

    /**
     * Create exception with error message for failing to load root page.
     *
     * @param int $pageId The page id.
     *
     * @return InitializePageContextFailed
     */
    public static function rootPageNotFound(int $pageId): self
    {
        return new self(sprintf('Could not load root page for given page ID "%s"', $pageId));
    }
}
