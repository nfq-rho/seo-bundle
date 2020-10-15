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

use Nfq\SeoBundle\EventListener\SeoRouterSubscriber;
use Nfq\SeoBundle\Routing\NotFoundResolverInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class NotFoundResolverPass
 * @package Nfq\SeoBundle\DependencyInjection\Compiler
 */
class NotFoundResolverPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->getParameter('nfq_seo.resolve_404_pages')) {
            $container->removeDefinition(NotFoundResolverInterface::class);
            $routerSubDef = $container->getDefinition(SeoRouterSubscriber::class);
            $args = $routerSubDef->getArguments();

            unset($args[2]);

            $routerSubDef->setArguments($args);
        }
    }
}
