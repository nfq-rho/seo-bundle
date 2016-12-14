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
 * Class CacheServiceCompilerPass
 * @package Nfq\SeoBundle\DependencyInjection\Compiler
 */
class CacheServiceCompilerPass  implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('stash.seo_cache')) {
            return;
        }

        $definition = $container->getDefinition('nfq_seo.url_manager');
        $definition->addMethodCall('setCache', [new Reference('stash.seo_cache')]);
    }
}
