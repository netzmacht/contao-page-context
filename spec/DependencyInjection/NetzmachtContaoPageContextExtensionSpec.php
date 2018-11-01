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

namespace spec\Netzmacht\Contao\PageContext\DependencyInjection;

use function dirname;
use Netzmacht\Contao\PageContext\DependencyInjection\NetzmachtContaoPageContextExtension;
use Netzmacht\Contao\PageContext\EventListener\PageContextListener;
use Netzmacht\Contao\PageContext\Request\PageContextFactory;
use Netzmacht\Contao\PageContext\Request\PageContextInitializer;
use Netzmacht\Contao\PageContext\Request\PageIdDeterminator;
use Netzmacht\Contao\PageContext\Security\PageContextVoter;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

final class NetzmachtContaoPageContextExtensionSpec extends ObjectBehavior
{
    public function it_is_initializable(): void
    {
        $this->shouldHaveType(NetzmachtContaoPageContextExtension::class);
    }

    public function it_loads_resources_and_registers_services(ContainerBuilder $container): void
    {
        $rootPath = dirname(dirname(__DIR__)) . '/src';

        $container->fileExists($rootPath . '/DependencyInjection/../Resources/config/services.xml')
            ->shouldBeCalled()
            ->willReturn(true);

        $container->fileExists($rootPath . '/DependencyInjection/../Resources/config/listener.xml')
            ->shouldBeCalled()
            ->willReturn(true);

        $container->hasExtension("http://symfony.com/schema/dic/services")->willReturn(false);

        $container->setDefinition(PageIdDeterminator::class, Argument::type(Definition::class))->shouldBeCalled();
        $container->setDefinition(PageContextFactory::class, Argument::type(Definition::class))->shouldBeCalled();
        $container->setDefinition(PageContextInitializer::class, Argument::type(Definition::class))->shouldBeCalled();
        $container->setDefinition(PageContextVoter::class, Argument::type(Definition::class))->shouldBeCalled();
        $container->setDefinition(PageContextListener::class, Argument::type(Definition::class))->shouldBeCalled();

        $this->load([], $container);
    }
}
