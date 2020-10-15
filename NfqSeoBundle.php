<?php declare(strict_types=1);

/**
 * This file is part of the "NFQ Bundles" package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nfq\SeoBundle;

use Nfq\SeoBundle\DependencyInjection\Compiler\NotFoundResolverPass;
use Nfq\SeoBundle\DependencyInjection\Compiler\SeoGeneratorPass;
use Nfq\SeoBundle\DependencyInjection\Compiler\SeoInvalidatorPass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class NfqSeoBundle
 * @package Nfq\SeoBundle
 */
class NfqSeoBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new NotFoundResolverPass(), PassConfig::TYPE_REMOVE);
        $container->addCompilerPass(new SeoGeneratorPass());
        $container->addCompilerPass(new SeoInvalidatorPass());
    }
}
