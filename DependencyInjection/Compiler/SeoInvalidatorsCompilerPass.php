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
 * Class SeoInvalidatorsCompilerPass
 * @package Nfq\SeoBundle\DependencyInjection\Compiler
 */
class SeoInvalidatorsCompilerPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('nfq_seo.router')) {
            return;
        }

        $definition = $container->getDefinition('nfq_seo.url_invalidator_manager');
        foreach ($container->findTaggedServiceIds('seo.invalidator') as $id => $attributes) {
            foreach ($attributes as $attribute) {
                $invalidatorDef = $container->getDefinition($id);
                $invalidatorDef->setLazy(true);
                $invalidatorDef->setPublic(false);

                $definition->addMethodCall('addInvalidator',
                    [new Reference($id), $attribute['route_name'], $attribute['entity']]);
            }
        }
    }
}
