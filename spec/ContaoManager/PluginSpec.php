<?php

declare(strict_types=1);

namespace spec\Netzmacht\Contao\PageContext\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Netzmacht\Contao\PageContext\ContaoManager\Plugin;
use Netzmacht\Contao\PageContext\NetzmachtContaoPageContextBundle;
use Netzmacht\Contao\Toolkit\Bundle\NetzmachtContaoToolkitBundle as NetzmachtContaoToolkit3Bundle;
use Netzmacht\Contao\Toolkit\NetzmachtContaoToolkitBundle as NetzmachtContaoToolkit4Bundle;
use PhpSpec\ObjectBehavior;

use function class_exists;

final class PluginSpec extends ObjectBehavior
{
    public function it_is_initializable(): void
    {
        $this->shouldHaveType(Plugin::class);
    }

    public function it_defines_loaded_bundles(ParserInterface $parser): void
    {
        /** @psalm-suppress UndefinedClass */
        $toolkitBundle = class_exists(NetzmachtContaoToolkit4Bundle::class)
            ? NetzmachtContaoToolkit4Bundle::class
            : NetzmachtContaoToolkit3Bundle::class;

        $this->getBundles($parser)->shouldBeArray();
        $this->getBundles($parser)->shouldHaveCount(1);

        $this->getBundles($parser)[0]
            ->shouldBeAnInstanceOf(BundleConfig::class);

        $this->getBundles($parser)[0]->getName()
            ->shouldReturn(NetzmachtContaoPageContextBundle::class);

        $this->getBundles($parser)[0]->getLoadAfter()
            ->shouldReturn([ContaoCoreBundle::class, $toolkitBundle]);
    }
}
