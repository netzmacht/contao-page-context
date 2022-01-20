<?php

declare(strict_types=1);

namespace Netzmacht\Contao\PageContext\Request;

use Contao\PageModel;
use Netzmacht\Contao\PageContext\Exception\InitializePageContextFailed;
use Netzmacht\Contao\Toolkit\Data\Model\RepositoryManager;

final class PageContextFactory
{
    /**
     * Repository manager.
     *
     * @var RepositoryManager
     */
    private $repositoryManager;

    /**
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
     * @throws InitializePageContextFailed When creating page context failed.
     */
    public function __invoke(int $pageId): PageContext
    {
        $repository = $this->repositoryManager->getRepository(PageModel::class);
        $pageModel  = $repository->find($pageId);

        if (! $pageModel instanceof PageModel) {
            throw InitializePageContextFailed::pageNotFound($pageId);
        }

        $pageModel->loadDetails();
        /** @psalm-suppress RedundantCastGivenDocblockType - Contao doc type issue */
        $rootPage = $repository->find((int) $pageModel->rootId);

        if (! $rootPage instanceof PageModel) {
            throw InitializePageContextFailed::rootPageNotFound((int) $pageModel->id);
        }

        return new PageContext($pageModel, $rootPage);
    }
}
