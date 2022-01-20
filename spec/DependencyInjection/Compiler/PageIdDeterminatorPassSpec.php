<?php

declare(strict_types=1);

namespace spec\Netzmacht\Contao\PageContext\DependencyInjection\Compiler;

use Netzmacht\Contao\PageContext\DependencyInjection\Compiler\PageIdDeterminatorPass;
use Netzmacht\Contao\PageContext\Request\PageIdDeterminator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

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

    public function it_assigns_tagged_determinators_to_determinator_service(
        ContainerBuilder $container,
        Definition $definition,
        Definition $referenceDefinition,
        ParameterBagInterface $parameterBag
    ): void {
        $container->hasDefinition(PageIdDeterminator::class)
            ->shouldBeCalled()
            ->willReturn(true);

        $container->getParameterBag()
            ->willReturn($parameterBag);

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

        $container
            ->getDefinition('foo')
            ->willReturn($referenceDefinition);

        $container->findTaggedServiceIds(PageIdDeterminator::class, true)->willReturn($references);

        $this->process($container);
    }
}
