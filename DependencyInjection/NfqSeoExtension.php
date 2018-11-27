<?php declare(strict_types=1);

/**
 * This file is part of the "NFQ Bundles" package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nfq\SeoBundle\DependencyInjection;

use Nfq\SeoBundle\Routing\SeoRouter;
use Nfq\SeoBundle\Service\AlternatesManager;
use Nfq\SeoBundle\Service\SeoManager;
use Nfq\SeoBundle\Twig\Extension\SeoExtension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Class NfqSeoExtension
 * @package Nfq\SeoBundle\DependencyInjection
 */
class NfqSeoExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');

        $this->configureSeoRouter($container, $config);
        $this->configureUrlManager($container, $config);
        $this->configureTwigExtension($container, $config);
        $this->configureAlternatesManager($container, $config);
        $this->configureSeoPage($container, $config['page']);
    }

    private function configureSeoPage(ContainerBuilder $container, array $config): void
    {
        return ;
        $definition = $container->getDefinition($config['default']);
        $definition->addMethodCall('setTitle', [$config['title'], ['translatable' => true]]);
        $definition->addMethodCall('setMetas', [$config['metas']]);
        $definition->addMethodCall('setHeadAttributes', [$config['head']]);
        $definition->addMethodCall('setHtmlAttributes', [$config['head']]);
        $definition->addMethodCall('setLinkOptions', [$config['rel_options']]);

        $container->setAlias('nfq_seo.page', $config['default']);
    }

    private function configureUrlManager(ContainerBuilder $container, array $config): void
    {
        $container
            ->getDefinition(SeoManager::class)
            ->addMethodCall('setSlugSeparator', [$config['slug_separator']])
            ->addMethodCall('setPathSeparator', [$config['path_separator']]);
    }

    private function configureAlternatesManager(ContainerBuilder $container, array $config): void
    {
        $container
            ->getDefinition(AlternatesManager::class)
            ->addMethodCall('setAlternateLocaleMapping', [$config['alternate_url_locale_mapping']]);
    }

    private function configureTwigExtension(ContainerBuilder $container, array $config): void
    {
        $container
            ->getDefinition(SeoExtension::class)
            ->addMethodCall('setEncoding', [$config['page']['encoding']])
            ->addMethodCall('setDefaultLocale', ['%nfq_seo.default_locale%']);
    }

    private function configureSeoRouter(ContainerBuilder $container, array $config): void
    {
        $definition = $container->getDefinition(SeoRouter::class);

        if (isset($config['default_locale'])) {
            $container->setParameter('nfq_seo.default_locale', $config['default_locale']);
            $definition->addMethodCall('setDefaultLocale', ['%nfq_seo.default_locale%']);
        } else {
            throw new \InvalidArgumentException("No default_locale has been defined");
        }

        $definition
            ->addMethodCall('setSlugSeparator', [$config['slug_separator']])
            ->addMethodCall('setPathSeparator', [$config['path_separator']])
            ->addMethodCall('setMissingUrlStrategy', [$config['missing_url_strategy']])
            ->addMethodCall('setNotFoundMessage', [$config['invalid_url_exception_message']]);
    }
}
