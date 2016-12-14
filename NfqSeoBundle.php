<?php
/**
 * This file is part of the "NFQ Bundles" package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nfq\SeoBundle;

use Nfq\SeoBundle\DependencyInjection\Compiler\CacheServiceCompilerPass;
use Nfq\SeoBundle\DependencyInjection\Compiler\OverrideRoutingCompilerPass;
use Nfq\SeoBundle\DependencyInjection\Compiler\SeoGeneratorsCompilerPass;
use Nfq\SeoBundle\DependencyInjection\Compiler\SeoInvalidatorsCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class NfqSeoBundle extends Bundle
{
    /**
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new OverrideRoutingCompilerPass());

        parent::build($container);

        $container->addCompilerPass(new CacheServiceCompilerPass());
        $container->addCompilerPass(new SeoGeneratorsCompilerPass());
        $container->addCompilerPass(new SeoInvalidatorsCompilerPass());
    }
}
