<?php

declare(strict_types=1);

namespace Netzmacht\Contao\PageContext\EventListener;

use Contao\CoreBundle\Exception\AccessDeniedException;
use Netzmacht\Contao\PageContext\Request\PageContextFactory;
use Netzmacht\Contao\PageContext\Request\PageContextInitializer;
use Netzmacht\Contao\PageContext\Request\PageIdDeterminator;
use Netzmacht\Contao\PageContext\Security\PageContextVoter;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Class PageContextListener
 */
final class PageContextListener
{
    /**
     * Page id determinator.
     *
     * @var PageIdDeterminator
     */
    private $pagIdDeterminator;

    /**
     * Page context factory.
     *
     * @var PageContextFactory
     */
    private $contextFactory;

    /**
     * Page context initializer.
     *
     * @var PageContextInitializer
     */
    private $initializer;

    /**
     * Authorization checker.
     *
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * PageContextListener constructor.
     *
     * @param PageIdDeterminator            $pagIdDeterminator    Page id determinator.
     * @param PageContextFactory            $contextFactory       Page context factory.
     * @param PageContextInitializer        $initializer          Page context initializer.
     * @param AuthorizationCheckerInterface $authorizationChecker Authorization checker.
     */
    public function __construct(
        PageIdDeterminator $pagIdDeterminator,
        PageContextFactory $contextFactory,
        PageContextInitializer $initializer,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->pagIdDeterminator    = $pagIdDeterminator;
        $this->contextFactory       = $contextFactory;
        $this->initializer          = $initializer;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(KernelEvent $event): void
    {
        $request = $event->getRequest();

        if (!$this->pagIdDeterminator->supports($request)) {
            return;
        }

        $pageId  = $this->pagIdDeterminator->determinate($request);
        $context = ($this->contextFactory)($pageId);

        if (!$this->authorizationChecker->isGranted(PageContextVoter::VIEW, $context)) {
            throw new AccessDeniedException();
        }

        $request->attributes->set('pageContext', $context);
        $this->initializer->initialize($context, $request);
    }
}
