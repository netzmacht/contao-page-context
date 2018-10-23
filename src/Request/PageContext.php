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

namespace Netzmacht\Contao\PageContext\Request;

use Contao\PageModel;

/**
 * Class PageContext describes a current page
 */
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
     * PageContext constructor.
     *
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
     *
     * @return PageModel
     */
    public function page(): PageModel
    {
        return $this->pageModel;
    }

    /**
     * Get the root page model.
     *
     * @return PageModel
     */
    public function rootPage(): PageModel
    {
        return $this->rootPage;
    }
}
