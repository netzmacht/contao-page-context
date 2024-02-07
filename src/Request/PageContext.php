<?php

declare(strict_types=1);

namespace Netzmacht\Contao\PageContext\Request;

use Contao\PageModel;

final class PageContext
{
    /**
     * @param PageModel $pageModel Page model.
     * @param PageModel $rootPage  Root page model.
     */
    public function __construct(private readonly PageModel $pageModel, private readonly PageModel $rootPage)
    {
    }

    /**
     * Get the page model.
     */
    public function page(): PageModel
    {
        return $this->pageModel;
    }

    /**
     * Get the root page model.
     */
    public function rootPage(): PageModel
    {
        return $this->rootPage;
    }
}
