<?php

declare(strict_types=1);

namespace spec\Netzmacht\Contao\PageContext\Request;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Model;
use Contao\PageModel;
use Netzmacht\Contao\PageContext\Exception\InitializePageContextFailed;
use Netzmacht\Contao\PageContext\Request\PageContext;
use Netzmacht\Contao\PageContext\Request\PageContextFactory;
use Netzmacht\Contao\Toolkit\Data\Model\Repository;
use Netzmacht\Contao\Toolkit\Data\Model\RepositoryManager;
use PhpSpec\ObjectBehavior;
use ReflectionClass;

final class PageContextFactorySpec extends ObjectBehavior
{
    private const PAGE_ID      = 5;
    private const ROOT_PAGE_ID = 1;

    public function let(
        RepositoryManager $repositoryManager,
        Repository $pageRepository,
        ContaoFramework $framework,
    ): void {
        $this->beConstructedWith($repositoryManager, $framework);

        $modelReflection = (new ReflectionClass(Model::class));
        if ($modelReflection->hasProperty('arrColumnCastTypes')) {
            $modelReflection->getProperty('arrColumnCastTypes')->setValue(['arrColumnCastTypes' => []]);
        }

        $repositoryManager->getRepository(PageModel::class)->willReturn($pageRepository);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(PageContextFactory::class);
    }

    public function it_creates_page_context_for_page_id(Repository $pageRepository): void
    {
        $pageReflection = new ReflectionClass(PageModel::class);
        $pageModel      = $pageReflection->newInstanceWithoutConstructor();
        $rootPage       = $pageReflection->newInstanceWithoutConstructor();

        $pageReflection->getProperty('blnDetailsLoaded')->setValue($pageModel, true);
        $pageModel->rootId = self::ROOT_PAGE_ID;

        $pageRepository->find(self::PAGE_ID)->willReturn($pageModel);
        $pageRepository->find(self::ROOT_PAGE_ID)->willReturn($rootPage);

        $this->__invoke(self::PAGE_ID)->shouldBeAnInstanceOf(PageContext::class);
        $this->__invoke(self::PAGE_ID)->page()->shouldReturn($pageModel);
        $this->__invoke(self::PAGE_ID)->rootPage()->shouldReturn($rootPage);
    }

    public function it_fails_creating_page_context_from_page_id_if_page_does_not_exist(Repository $pageRepository): void
    {
        $pageRepository->find(self::PAGE_ID)->willReturn(null);

        $this->shouldThrow(InitializePageContextFailed::class)->during('__invoke', [self::PAGE_ID]);
    }

    public function it_fails_creating_page_context_from_page_id_if_page_doesnt_have_root_page(
        Repository $pageRepository,
    ): void {
        $pageReflection = new ReflectionClass(PageModel::class);
        $pageModel      = $pageReflection->newInstanceWithoutConstructor();

        $pageReflection->getProperty('blnDetailsLoaded')->setValue($pageModel, true);
        $pageModel->rootId = self::ROOT_PAGE_ID;

        $pageRepository->find(self::PAGE_ID)->willReturn($pageModel);
        $pageRepository->find(self::ROOT_PAGE_ID)->willReturn(null);

        $this->shouldThrow(InitializePageContextFailed::class)->during('__invoke', [self::PAGE_ID]);
    }
}
