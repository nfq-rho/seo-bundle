<?php
/**
 * This file is part of the "NFQ Bundles" package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nfq\SeoBundle\Model;

/**
 * Interface SeoSlugInterface
 * @package Nfq\SeoBundle\Model
 */
interface SeoSlugInterface
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
     * @param string $prefix
     * @return $this
     */
    public function setPrefix($prefix);

    /**
     * @return string
     */
    public function getPrefix();

    /**
     * @param string $entityId
     * @return $this
     */
    public function setEntityId($entityId = null);

    /**
     * @return string
     */
    public function getEntityId();

    /**
     * @param string $locale
     * @return $this
     */
    public function setLocale($locale);

    /**
     * @return string
     */
    public function getLocale();

    /**
     * Route parts for seo url, which are later glued together. The order of these parts
     * is important.
     *  
     *
     * @param array $parts
     * @return $this
     */
    public function setRouteParts(array $parts);

    /**
     * @return array
     */
    public function getRouteParts();

    /**
     * @param array $parts
     * @return $this
     */
    public function setQueryParts(array $parts);

    /**
     * @return array
     */
    public function getQueryParts();
}
