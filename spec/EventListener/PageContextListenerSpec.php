<?php

declare(strict_types=1);

namespace spec\Netzmacht\Contao\PageContext\EventListener;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\LayoutModel;
use Contao\Model;
use Contao\PageModel;
use Netzmacht\Contao\PageContext\EventListener\PageContextListener;
use Netzmacht\Contao\PageContext\Request\PageContext;
use Netzmacht\Contao\PageContext\Request\PageContextFactory;
use Netzmacht\Contao\PageContext\Request\PageContextInitializer;
use Netzmacht\Contao\PageContext\Request\PageIdDeterminator;
use Netzmacht\Contao\PageContext\Security\PageContextVoter;
use Netzmacht\Contao\Toolkit\Data\Model\Repository;
use Netzmacht\Contao\Toolkit\Data\Model\RepositoryManager;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use ReflectionClass;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class PageContextListenerSpec extends ObjectBehavior
{
    private const PAGE_ID = 5;

    private const ROOT_PAGE_ID = 1;

    private const LAYOUT_ID = 3;

    public function let(
        PageIdDeterminator $pageIdDeterminator,
        RepositoryManager $repositoryManager,
        AuthorizationCheckerInterface $authorizationChecker,
        PageContextInitializer $initializer,
        KernelEvent $event,
        Request $request,
        ParameterBag $attributes,
        Repository $pageRepository,
        Repository $layoutRepository,
        ContaoFramework $framework,
    ): void {
        $contextFactory = new PageContextFactory(
            $repositoryManager->getWrappedObject(),
            $framework->getWrappedObject(),
        );

        $modelReflection = (new ReflectionClass(Model::class));
        if ($modelReflection->hasProperty('arrColumnCastTypes')) {
            $modelReflection->getProperty('arrColumnCastTypes')->setValue(['arrColumnCastTypes' => []]);
        }

        $this->beConstructedWith($pageIdDeterminator, $contextFactory, $initializer, $authorizationChecker);

        $pageIdDeterminator->determinate(Argument::type(Request::class))
            ->willReturn(self::PAGE_ID);

        $repositoryManager->getRepository(PageModel::class)
            ->willReturn($pageRepository);

        $repositoryManager->getRepository(LayoutModel::class)->willReturn($layoutRepository);

        $pageReflection = new ReflectionClass(PageModel::class);
        $pageModel      = $pageReflection->newInstanceWithoutConstructor();
        $rootPageModel  = $pageReflection->newInstanceWithoutConstructor();

        $pageReflection->getProperty('blnDetailsLoaded')->setValue($pageModel, true);
        $pageModel->rootId = self::ROOT_PAGE_ID;
        $pageModel->layout = self::LAYOUT_ID;

        $pageRepository->find(self::PAGE_ID)->willReturn($pageModel);
        $pageRepository->find(self::ROOT_PAGE_ID)->willReturn($rootPageModel);

        $authorizationChecker->isGranted(PageContextVoter::VIEW, Argument::type(PageContext::class))->willReturn(true);

        $request->attributes = $attributes;

        $event->getRequest()->willReturn($request);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(PageContextListener::class);
    }

    public function it_break_with_no_matching_page_id_determinator(
        PageIdDeterminator $pageIdDeterminator,
        KernelEvent $event,
    ): void {
        $pageIdDeterminator->match(Argument::type(Request::class))
            ->shouldBeCalled()
            ->willReturn(false);

        $pageIdDeterminator->determinate(Argument::type(Request::class))
            ->shouldNotBeCalled();

        $this->__invoke($event);
    }

    public function it_initializes_page_context_for_determinated_page_id(
        PageIdDeterminator $pageIdDeterminator,
        PageContextInitializer $initializer,
        KernelEvent $event,
    ): void {
        $pageIdDeterminator->match(Argument::type(Request::class))
            ->shouldBeCalled()
            ->willReturn(true);

        $pageIdDeterminator->determinate(Argument::type(Request::class))
            ->shouldBeCalled();

        $initializer->initialize(Argument::type(PageContext::class), Argument::type(Request::class))
            ->shouldBeCalled();

        $this->__invoke($event);
    }
}
