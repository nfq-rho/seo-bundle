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
 * Class ChainNotFoundResolver
 * @package Nfq\SeoBundle\Routing
 */
class ChainedNotFoundResolver implements NotFoundResolverInterface
{
    /** @var NotFoundResolverInterface[] */
    private $resolvers;

    public function __construct(array $resolvers)
    {
        $this->resolvers = $resolvers;
    }

    public function resolve(Request $request): ?string
    {
        $newUri = null;

        foreach ($this->resolvers as $resolver) {
            $newUri = $resolver->resolve($request);

            if (null !== $newUri) {
                break;
            }
        }

        return $newUri;
    }
}
