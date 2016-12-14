<?php
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
    /**
     * @param string $routeName
     * @return $this
     */
    public function setRouteName($routeName);

    /**
     * @return string
     */
    public function getRouteName();

    /**
     * Get entity manager.
     *
     * @return EntityManagerInterface
     */
    public function getEntityManager();

    /**
     * Set entity manager.
     *
     * @param EntityManagerInterface $em
     * @return $this
     */
    public function setEntityManager(EntityManagerInterface $em);

    /**
     * Invalidate seo url for given entity based on given change-set
     *
     * @param object $entity
     * @param array $changeSet
     * @return $this
     */
    public function invalidate($entity, array $changeSet);

    /**
     * Remove seo urls for given entity
     *
     * @param object $entity
     * @return $this
     */
    public function remove($entity);
}
