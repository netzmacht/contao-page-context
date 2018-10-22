<?php

declare(strict_types=1);

namespace Netzmacht\Contao\PageContext\Request;

use Contao\PageModel;

/**
 * Class PageContext describes a current page
 *
 * @package Netzmacht\Contao\PageContext\Request
 */
final class PageContext
{
    /**
     * @var PageModel
     */
    private $pageModel;

    /**
     * @var PageModel
     */
    private $rootPage;

    /**
     * PageContext constructor.
     *
     * @param PageModel $pageModel
     * @param PageModel $rootPage
     */
    public function __construct(PageModel $pageModel, PageModel $rootPage)
    {
        $this->pageModel = $pageModel;
        $this->rootPage = $rootPage;
    }

    public function page(): PageModel
    {
        return $this->pageModel;
    }

    public function rootPage(): PageModel
    {
        return $this->rootPage;
    }
}
