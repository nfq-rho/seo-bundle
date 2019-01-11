<?php declare(strict_types=1);

/**
 * This file is part of the "NFQ Bundles" package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nfq\SeoBundle\Invalidator\Object;

use Doctrine\ORM\EntityManagerInterface;

/**
 * Interface ObjectFactoryInterface
 * @package Nfq\SeoBundle\Invalidator\Object
 */
interface ObjectFactoryInterface
{
    public static function buildInvalidationObject(
        EntityManagerInterface $em,
        $entity,
        ?array $changeSet
    ): InvalidationObjectInterface;
}
