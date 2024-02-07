<?php

declare(strict_types=1);

namespace Netzmacht\Contao\PageContext\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Netzmacht\Contao\PageContext\NetzmachtContaoPageContextBundle;
use Netzmacht\Contao\Toolkit\Bundle\NetzmachtContaoToolkitBundle as NetzmachtContaoToolkit3Bundle;
use Netzmacht\Contao\Toolkit\NetzmachtContaoToolkitBundle as NetzmachtContaoToolkit4Bundle;

use function class_exists;

final class Plugin implements BundlePluginInterface
{
    /**
     * {@inheritDoc}
     */
    public function getBundles(ParserInterface $parser): array
    {
        /** @psalm-suppress UndefinedClass */
        $toolkitBundle = class_exists(NetzmachtContaoToolkit4Bundle::class)
            ? NetzmachtContaoToolkit4Bundle::class
            : NetzmachtContaoToolkit3Bundle::class;

        return [
            BundleConfig::create(NetzmachtContaoPageContextBundle::class)
                ->setLoadAfter([ContaoCoreBundle::class, $toolkitBundle]),
        ];
    }
}
