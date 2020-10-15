<?php declare(strict_types=1);

/**
 * This file is part of the "NFQ Bundles" package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nfq\SeoBundle\Traits;

use Nfq\SeoBundle\Utils\SeoHelper;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\ChainAdapter;

/**
 * Trait SeoCache
 * @package Nfq\SeoBundle\Traits
 */
trait SeoCache
{
    /** @var CacheItemPoolInterface */
    private $pool;

    public function canCache(): bool
    {
        return (!SeoHelper::isCli() && null !== $this->pool);
    }

    /**
     * @param CacheItemPoolInterface[] $adapters
     */
    public function setPool(array $adapters): void
    {
        $this->pool = new ChainAdapter($adapters);
    }

    public function getCache(): CacheItemPoolInterface
    {
        return $this->pool;
    }
}
