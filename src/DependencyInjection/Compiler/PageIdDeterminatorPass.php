<?php

declare(strict_types=1);

namespace Netzmacht\Contao\PageContext\DependencyInjection\Compiler;

use Netzmacht\Contao\PageContext\Request\PageIdDeterminator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PriorityTaggedServiceTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use function array_merge;

final class PageIdDeterminatorPass implements CompilerPassInterface
{
    use PriorityTaggedServiceTrait;

    public function process(ContainerBuilder $container): void
    {
        if (! $container->hasDefinition(PageIdDeterminator::class)) {
            return;
        }

        $definition = $container->getDefinition(PageIdDeterminator::class);
        $argument   = (array) $definition->getArgument(0);
        $argument   = array_merge($argument, $this->findAndSortTaggedServices(PageIdDeterminator::class, $container));

        $definition->setArgument(0, $argument);
    }
}
