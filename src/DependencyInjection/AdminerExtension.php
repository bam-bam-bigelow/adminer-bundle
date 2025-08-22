<?php

declare(strict_types=1);

namespace YourVendor\AdminerBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class AdminerExtension extends Extension implements PrependExtensionInterface
{
    public function prepend(ContainerBuilder $container): void {
        $container->prependExtensionConfig('twig', [
            'paths' => [
            ],
        ]);
    }

    public function load(array $configs, ContainerBuilder $container): void {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
        );
        $loader->load('services.yaml');
    }
}
