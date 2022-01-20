<?php

declare(strict_types=1);

namespace spec\Netzmacht\Contao\PageContext\Request;

use Contao\CoreBundle\Framework\Adapter;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\CoreBundle\Image\PictureFactoryInterface;
use Netzmacht\Contao\PageContext\Request\ContaoPageContextInitializer;
use Netzmacht\Contao\Toolkit\Callback\Invoker;
use Netzmacht\Contao\Toolkit\Data\Model\RepositoryManager;
use PhpSpec\ObjectBehavior;
use Psr\Log\LoggerInterface;
use Symfony\Component\Translation\TranslatorInterface;

final class ContaoPageContextInitializerSpec extends ObjectBehavior
{
    public function let(
        TranslatorInterface $translator,
        ContaoFrameworkInterface $framework,
        PictureFactoryInterface $pictureFactory,
        RepositoryManager $repositoryManager,
        LoggerInterface $logger,
        Adapter $systemAdapter
    ): void {
        $this->beConstructedWith(
            $translator,
            $framework,
            new Invoker($systemAdapter),
            $pictureFactory,
            $repositoryManager,
            $logger
        );
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(ContaoPageContextInitializer::class);
    }
}
