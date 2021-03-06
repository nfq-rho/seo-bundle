<?php
/**
 * This file is part of the "NFQ Bundles" package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nfq\SeoBundle\Invalidator\Object;

/**
 * Interface ObjectFactoryInterface
 * @package Nfq\SeoBundle\Invalidator\Object
 */
interface ObjectFactoryInterface
{
    /**
     * @param object $entity
     * @param array $changeSet
     * @return InvalidationObjectInterface
     * @throws \InvalidArgumentException
     */
    public static function buildInvalidationObject($entity, array $changeSet);
}
