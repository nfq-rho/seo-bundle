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
 * Interface InvalidationObjectInterface
 * @package Nfq\SeoBundle\Invalidator\Object
 */
interface InvalidationObjectInterface
{
    /**
     * Where part by default has following query "su.route_name = :route_name AND su.locale = :locale"
     * @return string
     */
    public function getWherePart();

    /**
     * @return string
     */
    public function getJoinPart();

    /**
     * @return array
     */
    public function getWhereParams();

    /**
     * @return object
     */
    public function getEntity();

    /**
     * @return string
     */
    public function getLocale();

    /**
     * @return boolean
     */
    public function hasChanges();

    /**
     * @return int
     */
    public function getInvalidationStatus();
}
