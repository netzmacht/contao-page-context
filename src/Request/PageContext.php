<?php

declare(strict_types=1);

namespace Netzmacht\Contao\PageContext\Request;

use Contao\PageModel;

final class PageContext
{
    /**
     * Page model.
     *
     * @var PageModel
     */
    private $pageModel;

    /**
     * Root page model.
     *
     * @var PageModel
     */
    private $rootPage;

    /**
     * @param PageModel $pageModel Page model.
     * @param PageModel $rootPage  Root page model.
     */
    public function __construct(PageModel $pageModel, PageModel $rootPage)
    {
        $this->pageModel = $pageModel;
        $this->rootPage  = $rootPage;
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
