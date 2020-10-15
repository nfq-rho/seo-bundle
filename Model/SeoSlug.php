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
 * Class SeoSlug
 * @package Nfq\SeoBundle\Model
 */
class SeoSlug implements SeoSlugInterface
{
    /** @var int */
    private $entityId;

    /** @var string */
    private $prefix;

    /** @var string */
    private $locale;

    /** @var string */
    private $routeName;

    /** @var string[] */
    private $routeParts = [];

    /** @var string[] */
    private $routeQueryParts = [];

    public function setPrefix(string $prefix): SeoSlugInterface
    {
        $this->prefix = $prefix;
        return $this;
    }

    public function getPrefix(): string
    {
        return $this->prefix;
    }

    public function setEntityId(int $entityId = null): SeoSlugInterface
    {
        if (null === $entityId) {
            $entityId = $this->generateEntityId();
        }

        $this->entityId = $entityId;
        return $this;
    }

    public function getEntityId(): int
    {
        return $this->entityId;
    }

    public function setLocale(string $locale): SeoSlugInterface
    {
        $this->locale = $locale;
        return $this;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function getRouteName(): string
    {
        return $this->routeName;
    }

    public function setRouteName(string $routeName): SeoSlugInterface
    {
        $this->routeName = $routeName;
        return $this;
    }

    public function setRouteParts(array $parts): SeoSlugInterface
    {
        $this->routeParts = $parts;
        return $this;
    }

    public function getRouteParts(): array
    {
        return $this->routeParts;
    }

    public function setQueryParts(array $parts): SeoSlugInterface
    {
        $this->routeQueryParts = $parts;
        return $this;
    }

    public function getQueryParts(): array
    {
        return $this->routeQueryParts;
    }

    /**
     * Generate custom entity ID if slug is not related to any real entity
     */
    private function generateEntityId(): int
    {
        return (int)sprintf('%u', crc32(json_encode($this->routeQueryParts)));
    }
}
