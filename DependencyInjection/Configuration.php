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
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('nfq_seo');

        $rootNode
            ->children()
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
                        ->scalarNode('default')->defaultValue('nfq_seo.page.default')->end()
                        ->scalarNode('title')->defaultValue('nfq_seo.default_title')->end()
                        ->arrayNode('metas')
                            ->useAttributeAsKey('id')
                            ->prototype('array')
                                ->useAttributeAsKey('id')
                                ->prototype('variable')->end()
                            ->end()
                        ->end()
                        ->arrayNode('html')
                            ->useAttributeAsKey('id')
                            ->prototype('variable')->end()
                        ->end()
                        ->arrayNode('head')
                            ->useAttributeAsKey('id')
                            ->prototype('variable')->end()
                        ->end()
                    ->end()
            ->end();

        return $treeBuilder;
    }
}
