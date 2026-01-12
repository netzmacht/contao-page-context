<?php

declare(strict_types=1);

namespace Netzmacht\Contao\PageContext;

use Netzmacht\Contao\PageContext\DependencyInjection\Compiler\PageIdDeterminatorPass;
use Override;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class NetzmachtContaoPageContextBundle extends Bundle
{
    #[Override]
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new PageIdDeterminatorPass());
    }
}
