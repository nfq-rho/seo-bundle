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

/**
 * Trait SeoCache
 * @package Nfq\SeoBundle\Traits
 */
trait SeoCache
{
    /** @var CacheItemPoolInterface */
    private $cache;

    public function canCache(): bool
    {
        return (!SeoHelper::isCli() && $this->cache);
    }

    public function setCache(CacheItemPoolInterface $cachePool): void
    {
        $this->cache = $cachePool;
    }

    public function getCache(): CacheItemPoolInterface
    {
        return $this->cache;
    }
}
