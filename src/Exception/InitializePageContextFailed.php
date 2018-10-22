<?php

namespace Netzmacht\Contao\PageContext\Exception;

use RuntimeException;

/**
 * Class InitializePageContextFailed
 */
final class InitializePageContextFailed extends RuntimeException
{
    public static function determinatePageIdFailed(): self
    {
        return new self('Determinating page id failed.');
    }

    public static function invalidPageId(int $pageId): self
    {
        return new self(sprintf('Could not fetch model from determinated page id "%s"', $pageId));
    }

    public static function noRootPage(int $pageId): self
    {
        return new self(sprintf('Could not load root page for given page ID "%s"', $pageId));
    }
}
