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

namespace spec\Netzmacht\Contao\PageContext\DependencyInjection\Compiler;

use Netzmacht\Contao\PageContext\DependencyInjection\Compiler\PageIdDeterminatorPass;
use Netzmacht\Contao\PageContext\Request\PageIdDeterminator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

final class PageIdDeterminatorPassSpec extends ObjectBehavior
{
    public function it_is_initializable(): void
    {
        $this->shouldHaveType(PageIdDeterminatorPass::class);
    }

    public function it_breaks_if_page_id_determinator_service_not_exists(ContainerBuilder $container): void
    {
        $container->hasDefinition(PageIdDeterminator::class)
            ->shouldBeCalled()
            ->willReturn(false);

        $container->getDefinition(PageIdDeterminator::class)
            ->shouldNotBeCalled();

        $this->process($container);
    }

    public function it_assigns_tagged_determinators_to_determinator_service_(
        ContainerBuilder $container,
        Definition $definition
    ): void {
        $container->hasDefinition(PageIdDeterminator::class)
            ->shouldBeCalled()
            ->willReturn(true);

        $definition->getClass()->willReturn(PageIdDeterminator\DelegatingPageIdDeterminator::class);
        $definition->getTags()->willReturn([['name' => PageIdDeterminator::class]]);

        $definition->getArgument(0)
            ->shouldBeCalled()
            ->willReturn([]);

        $definition->setArgument(0, Argument::type('array'))
            ->shouldBeCalled();

        $container->getDefinition(PageIdDeterminator::class)
            ->willReturn($definition);

        $references = ['foo' => []];

        $container->findTaggedServiceIds(PageIdDeterminator::class, true)->willReturn($references);

        $this->process($container);
    }
}
