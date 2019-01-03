<?php declare(strict_types=1);

/**
 * This file is part of the "NFQ Bundles" package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nfq\SeoBundle\Routing;

use Symfony\Component\HttpFoundation\Request;

/**
 * Interface NotFoundResolverInterface
 * @package Nfq\SeoBundle\Routing
 */
interface NotFoundResolverInterface
{
    public function resolve(Request $request): ?string;
}
