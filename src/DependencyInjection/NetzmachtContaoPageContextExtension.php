<?php

declare(strict_types=1);

namespace Netzmacht\Contao\PageContext\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

final class NetzmachtContaoPageContextExtension extends Extension
{
    /** {@inheritDoc} */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));

        $loader->load('services.xml');
        $loader->load('listener.xml');
    }
}
