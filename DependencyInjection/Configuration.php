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

use Nfq\SeoBundle\Page\SeoPage;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration
 * @package Nfq\SeoBundle\DependencyInjection
 */
class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('nfq_seo');
        $rootNode = method_exists($treeBuilder, 'getRootNode')
            ? $treeBuilder->getRootNode()
            : $treeBuilder->root('nfq_seo');

        $rootNode
            ->children()
                ->arrayNode('cache')
                    ->children()
                        ->integerNode('ttl')
                            ->treatNullLike(3600)
                            ->defaultValue(3600)
                        ->end()
                        ->arrayNode('adapters')
                            ->treatNullLike([])
                            ->scalarPrototype()->end()
                        ->end()
                    ->end()
                ->end()
                ->scalarNode('resolve_404_pages')->defaultFalse()->end()
                ->scalarNode('default_locale')->end()
                ->arrayNode('alternate_url_locale_mapping')
                    ->useAttributeAsKey('id')
                    ->prototype('variable')->end()
                ->end()
                ->scalarNode('slug_separator')->defaultValue('-')->end()
                ->scalarNode('path_separator')->defaultValue('/')->end()
                ->scalarNode('missing_url_strategy')->defaultValue('ignore')->end()
                ->scalarNode('invalid_url_exception_message')->isRequired()->cannotBeEmpty()->end()
                ->arrayNode('page')
                    ->children()
                        ->arrayNode('rel_options')
                            ->children()
                                ->arrayNode('allowed_canonical_parameters')
                                    ->prototype('variable')->end()
                                ->end()
                            ->end()
                        ->end()
                        ->scalarNode('encoding')->defaultValue('UTF-8')->end()
                        ->scalarNode('service')->defaultValue(SeoPage::class)->end()
                        ->scalarNode('title')->defaultValue('nfq_seo.default_title')->end()
                        ->arrayNode('title_extras')
                            ->treatNullLike([])
                            ->prototype('variable')->end()
                        ->end()
                        ->arrayNode('metas')
                            ->useAttributeAsKey('id')
                            ->prototype('array')
                                ->prototype('variable')->end()
                            ->end()
                        ->end()
                        ->arrayNode('html')
                            ->prototype('variable')->end()
                        ->end()
                        ->arrayNode('head')
                            ->prototype('variable')->end()
                        ->end()
                    ->end()
            ->end();

        return $treeBuilder;
    }
}
