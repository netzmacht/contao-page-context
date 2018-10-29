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
use Netzmacht\Contao\PageContext\Exception\InitializePageContextFailed;
use Netzmacht\Contao\Toolkit\Data\Model\RepositoryManager;

/**
 * Class PageContextFactory creates the page context from a request or page id
 */
final class PageContextFactory
{
    /**
     * Repository manager.
     *
     * @var RepositoryManager
     */
    private $repositoryManager;

    /**
     * PageContextFactory constructor.
     *
     * @param RepositoryManager $repositoryManager Repository manager.
     */
    public function __construct(RepositoryManager $repositoryManager)
    {
        $this->repositoryManager = $repositoryManager;
    }

    /**
     * Create page context from a given page id.
     *
     * @param int $pageId Page id for the page context.
     *
     * @return PageContext
     *
     * @throws InitializePageContextFailed When creating page context failed.
     */
    public function __invoke(int $pageId): PageContext
    {
        $repository = $this->repositoryManager->getRepository(PageModel::class);
        $pageModel  = $repository->find($pageId);

        if (!$pageModel instanceof PageModel) {
            throw InitializePageContextFailed::pageNotFound($pageId);
        }

        $pageModel->loadDetails();
        $rootPage = $repository->find((int) $pageModel->rootId);

        if (!$rootPage instanceof PageModel) {
            throw InitializePageContextFailed::rootPageNotFound($pageModel->id);
        }

        return new PageContext($pageModel, $rootPage);
    }
}
