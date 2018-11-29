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

use Nfq\SeoBundle\Invalidator\SeoInvalidatorManager;
use Nfq\SeoBundle\Routing\SeoRouter;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class SeoInvalidatorsCompilerPass
 * @package Nfq\SeoBundle\DependencyInjection\Compiler
 */
class SeoInvalidatorsCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition(SeoRouter::class)) {
            return;
        }

        $definition = $container->getDefinition(SeoInvalidatorManager::class);
        foreach ($container->findTaggedServiceIds('nfq_seo.invalidator') as $id => $attributes) {
            foreach ($attributes as $attribute) {
                $invalidatorDef = $container->getDefinition($id);
                $invalidatorDef->setLazy(true);

                $definition->addMethodCall(
                    'addInvalidator',
                    [
                        new Reference($id),
                        $attribute['route_name'],
                        $attribute['entity']
                    ]
                );
            }
        }
    }
}
