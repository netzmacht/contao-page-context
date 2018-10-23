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

namespace Netzmacht\Contao\PageContext\DependencyInjection\Compiler;

use Netzmacht\Contao\PageContext\Request\PageIdDeterminator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PriorityTaggedServiceTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class PageIdDeterminatorPass
 */
final class PageIdDeterminatorPass implements CompilerPassInterface
{
    use PriorityTaggedServiceTrait;

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition(PageIdDeterminator::class)) {
            return;
        }

        $definition = $container->getDefinition(PageIdDeterminator::class);
        $argument   = (array) $definition->getArgument(0);
        $argument   = array_merge($argument, $this->findAndSortTaggedServices(PageIdDeterminator::class, $container));

        $definition->setArgument(0, $argument);
    }
}
