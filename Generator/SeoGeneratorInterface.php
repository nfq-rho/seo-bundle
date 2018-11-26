<?php declare(strict_types=1);

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
    public function getEntityManager(): EntityManagerInterface;

    public function setEntityManager(EntityManagerInterface $em): SeoGeneratorInterface;

    public function setCurrentRouteName(string $routeName): SeoGeneratorInterface;

    public function getCurrentRouteName(): string;

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
    public function getHashParams(array $uriParams): array;

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
    public function getAllowedQueryParams(): array;
}
