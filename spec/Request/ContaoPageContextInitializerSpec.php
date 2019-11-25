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

namespace spec\Netzmacht\Contao\PageContext\Request;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\CoreBundle\Image\PictureFactoryInterface;
use Netzmacht\Contao\PageContext\Request\ContaoPageContextInitializer;
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
