<?php
/**
 * This file is part of the "NFQ Bundles" package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nfq\SeoBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class SeoGeneratorsCompilerPass
 * @package Nfq\SeoBundle\DependencyInjection\Compiler
 */
class SeoGeneratorsCompilerPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('nfq_seo.router')) {
            return;
        }

        $definition = $container->getDefinition('nfq_seo.url_generator_manager');
        foreach ($container->findTaggedServiceIds('seo.generator') as $id => $attributes) {
            foreach ($attributes as $attribute) {
                $generatorDef = $container->getDefinition($id);
                $generatorDef->setLazy(true);

                $definition->addMethodCall('addGenerator', [new Reference($id), $attribute['route_name']]);
            }
        }
    }
}
