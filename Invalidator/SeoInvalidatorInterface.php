<?php declare(strict_types=1);

/**
 * This file is part of the "NFQ Bundles" package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nfq\SeoBundle\Invalidator;

use Doctrine\ORM\EntityManagerInterface;

/**
 * Interface SeoInvalidatorInterface
 * @package Nfq\SeoBundle\Invalidator
 */
interface SeoInvalidatorInterface
{
    public function setRouteName(string $routeName): SeoInvalidatorInterface;

    public function getRouteName(): string;

    public function getEntityManager(): EntityManagerInterface;

    /**
     * Invalidate seo url for given entity based on given change-set
     *
     * @param object $entity
     * @param string[] $changeSet
     */
    public function invalidate($entity, array $changeSet): void;

    /**
     * Remove seo urls for given entity
     *
     * @param object $entity
     */
    public function remove($entity): void;
}
