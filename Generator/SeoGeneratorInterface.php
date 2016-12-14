<?php
/**
 * This file is part of the "NFQ Bundles" package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nfq\SeoBundle\Generator;

use Doctrine\ORM\EntityManagerInterface;
use Nfq\SeoBundle\Model\SeoSlugInterface;

/**
 * Interface SeoGeneratorInterface
 * @package Nfq\SeoBundle\Generator
 */
interface SeoGeneratorInterface
{
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
     * Adds route available route
     *
     * @param string $routeName
     * @return $this
     */
    public function setCurrentRouteName($routeName);

    /**
     * @return string
     */
    public function getCurrentRouteName();

    /**
     * Generates SEO url.
     *
     * @param array $params
     * @return SeoSlugInterface|false
     */
    public function generate(array $params);

    /**
     * Get data which will be used to generate slug hash.
     * $uriParams contains data from parse_url
     *
     * @param array $uriParams
     * @return array
     */
    public function getHashParams(array $uriParams);

    /**
     * Specify which query parameters can be used to distinguish URI from others, to make it some how unique that is.
     * These parameters will also be used to get correct stdHash when resolving SEO URI. If no params are needed,
     * an empty array should be returned
     *
     * Note that those parameters will become required in order to generate such URL, so additional logic
     * can be implemented via setMissingAllowedParameters()
     *
     * @return array
     */
    public function getAllowedQueryParams();
}
