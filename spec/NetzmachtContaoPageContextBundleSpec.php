<?php

declare(strict_types=1);

namespace spec\Netzmacht\Contao\PageContext;

use Netzmacht\Contao\PageContext\DependencyInjection\Compiler\PageIdDeterminatorPass;
use Netzmacht\Contao\PageContext\NetzmachtContaoPageContextBundle;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class NetzmachtContaoPageContextBundleSpec extends ObjectBehavior
{
    public function it_is_initializable(): void
    {
        $this->shouldHaveType(NetzmachtContaoPageContextBundle::class);
    }

    public function it_registers_compiler_passes(ContainerBuilder $container): void
    {
        $container->addCompilerPass(Argument::type(PageIdDeterminatorPass::class))
            ->shouldBeCalled();

        $this->build($container);
    }
}
