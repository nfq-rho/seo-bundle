<?php declare(strict_types=1);

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
    public function setRouteName(string $routeName): SeoSlugInterface;

    public function getRouteName(): string;

    public function setPrefix(string $prefix): SeoSlugInterface;

    public function getPrefix(): string;

    public function setEntityId(int $entityId = null): SeoSlugInterface;

    public function getEntityId(): int;

    public function setLocale(string $locale): SeoSlugInterface;

    public function getLocale(): string;

    /**
     * Route parts for seo url, which are later glued together. The order of these parts
     * is important.
     *
     * @param string[] $parts
     */
    public function setRouteParts(array $parts): SeoSlugInterface;

    /**
     * @return string[]
     */
    public function getRouteParts(): array;

    /**
     * @param string[] $parts
     * @return $this
     */
    public function setQueryParts(array $parts): SeoSlugInterface;

    /**
     * @return string[]
     */
    public function getQueryParts(): array;
}
