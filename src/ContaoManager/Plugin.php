<?php

declare(strict_types=1);

namespace Netzmacht\Contao\PageContext\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Netzmacht\Contao\PageContext\NetzmachtContaoPageContextBundle;
use Netzmacht\Contao\Toolkit\Bundle\NetzmachtContaoToolkitBundle;

final class Plugin implements BundlePluginInterface
{
    /**
     * {@inheritdoc}
     */
    public function getBundles(ParserInterface $parser): array
    {
        return [
            BundleConfig::create(NetzmachtContaoPageContextBundle::class)
                ->setLoadAfter([ContaoCoreBundle::class, NetzmachtContaoToolkitBundle::class]),
        ];
    }
}
