<?php

declare(strict_types=1);

namespace spec\Netzmacht\Contao\PageContext\Request;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Image\PictureFactoryInterface;
use Netzmacht\Contao\PageContext\Request\ContaoPageContextInitializer;
use Netzmacht\Contao\Toolkit\Data\Model\RepositoryManager;
use PhpSpec\ObjectBehavior;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Translation\LocaleAwareInterface;

final class ContaoPageContextInitializerSpec extends ObjectBehavior
{
    public function let(
        LocaleAwareInterface $translator,
        ContaoFramework $framework,
        PictureFactoryInterface $pictureFactory,
        RepositoryManager $repositoryManager,
        LoggerInterface $logger
    ): void {
        $this->beConstructedWith(
            $translator,
            $framework,
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
