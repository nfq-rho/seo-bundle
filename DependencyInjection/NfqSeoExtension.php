<?php
/**
 * This file is part of the "NFQ Bundles" package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nfq\SeoBundle\DependencyInjection;

use Nfq\SeoBundle\EventListener\SeoRouterSubscriber;
use Nfq\SeoBundle\Routing\SeoRouter;
use Nfq\SeoBundle\Twig\Extension\SeoExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class NfqSeoExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');

        $this->configureSeoRouter($container, $config);
        $this->configureUrlManager($container, $config);
        $this->configureTwigExtension($container, $config);
        $this->configureAlternatesManager($container, $config);
        $this->configureDefaultLocaleSubscriber($container, $config);
        $this->configureSeoPage($container, $config['page']);

        $this->configureClassesToCompile();
    }

    /**
     * @param ContainerBuilder $container
     * @param array $config
     */
    private function configureSeoPage(ContainerBuilder $container, array $config)
    {
        $definition = $container->getDefinition($config['default']);
        $definition->addMethodCall('setTitle', [$config['title'], ['translatable' => true]]);
        $definition->addMethodCall('setMetas', [$config['metas']]);
        $definition->addMethodCall('setHeadAttributes', [$config['head']]);
        $definition->addMethodCall('setHtmlAttributes', [$config['head']]);
        $definition->addMethodCall('setLinkOptions', [$config['rel_options']]);

        $container->setAlias('nfq_seo.page', $config['default']);
    }

    /**
     * Add classes to compile
     * @return void
     */
    private function configureClassesToCompile()
    {
        $this->addClassesToCompile([
            SeoRouter::class,
            SeoRouterSubscriber::class,
            SeoExtension::class,
        ]);
    }

    /**
     * @param ContainerBuilder $container
     * @param array $config
     */
    private function configureDefaultLocaleSubscriber(ContainerBuilder $container, array $config)
    {
        $container
            ->getDefinition('nfq_seo.default_locale_subscriber')
            ->addMethodCall('setDefaultLocale', ['%nfq_seo.default_locale%']);
    }

    /**
     * @param ContainerBuilder $container
     * @param array $config
     */
    private function configureUrlManager(ContainerBuilder $container, array $config)
    {
        $container
            ->getDefinition('nfq_seo.url_manager')
            ->addMethodCall('setSlugSeparator', [$config['slug_separator']])
            ->addMethodCall('setPathSeparator', [$config['path_separator']]);
    }

    /**
     * @param ContainerBuilder $container
     * @param array $config
     */
    private function configureAlternatesManager(ContainerBuilder $container, array $config)
    {
        $container
            ->getDefinition('nfq_seo.alternates_manager')
            ->addMethodCall('setAlternateLocaleMapping', [$config['alternate_url_locale_mapping']]);
    }

    /**
     * @param ContainerBuilder $container
     * @param array $config
     */
    private function configureTwigExtension(ContainerBuilder $container, array $config)
    {
        $container
            ->getDefinition('nfq_seo.twig_extension')
            ->addMethodCall('setEncoding', [$config['page']['encoding']])
            ->addMethodCall('setDefaultLocale', ['%nfq_seo.default_locale%']);
    }

    /**
     * @param ContainerBuilder $container
     * @param array $config
     */
    private function configureSeoRouter(ContainerBuilder $container, array $config)
    {
        $definition = $container->getDefinition('nfq_seo.router');

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
