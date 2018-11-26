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
use Stash\Interfaces\PoolInterface;

/**
 * Class SeoCache
 * @package Nfq\SeoBundle\Traits
 */
trait SeoCache
{
    /**
     * @var PoolInterface
     */
    private $cache;

    public function canCache(): bool
    {
        return (!SeoHelper::isCli() && $this->cache);
    }

    /**
     * @param PoolInterface $cachePool
     */
    public function setCache(PoolInterface $cachePool)
    {
        $this->cache = $cachePool;
    }

    /**
     * @return PoolInterface
     */
    public function getCache()
    {
        return $this->cache;
    }
}
