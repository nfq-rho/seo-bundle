<?php declare(strict_types=1);

/**
 * This file is part of the "NFQ Bundles" package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nfq\SeoBundle\DependencyInjection\Compiler;

use Nfq\SeoBundle\Generator\SeoGeneratorManager;
use Nfq\SeoBundle\Routing\SeoRouter;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class SeoGeneratorsCompilerPass
 * @package Nfq\SeoBundle\DependencyInjection\Compiler
 */
class SeoGeneratorsCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition(SeoRouter::class)) {
            return;
        }

        $definition = $container->getDefinition(SeoGeneratorManager::class);
        foreach ($container->findTaggedServiceIds('nfq_seo.generator') as $id => $attributes) {
            foreach ($attributes as $attribute) {
                $generatorDef = $container->getDefinition($id);
                $generatorDef->setLazy(true);

                $definition->addMethodCall(
                    'addGenerator',
                    [
                        new Reference($id),
                        $attribute['route_name']
                    ]
                );
            }
        }
    }
}
