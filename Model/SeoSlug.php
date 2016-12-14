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
 * Class SeoSlug
 * @package Nfq\SeoBundle\Model
 */
class SeoSlug implements SeoSlugInterface
{
    /**
     * @var string
     */
    private $entityId;

    /**
     * @var string
     */
    private $prefix;

    /**
     * @var string
     */
    private $locale;

    /**
     * @var string
     */
    private $routeName;

    /**
     * @var array
     */
    private $routeParts;

    /**
     * @var array
     */
    private $routeQueryParts;

    /**
     * @inheritdoc
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * @inheritdoc
     */
    public function setEntityId($entityId = null)
    {
        if (is_null($entityId)) {
            $entityId = $this->generateEntityId();
        }

        $this->entityId = (string)$entityId;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getEntityId()
    {
        return (string)$this->entityId;
    }

    /**
     * @inheritdoc
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @inheritdoc
     */
    public function getRouteName()
    {
        return $this->routeName;
    }

    /**
     * @inheritdoc
     */
    public function setRouteName($routeName)
    {
        $this->routeName = $routeName;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setRouteParts(array $parts)
    {
        $this->routeParts = $parts;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getRouteParts()
    {
        return (array)$this->routeParts;
    }

    /**
     * @inheritdoc
     */
    public function setQueryParts(array $parts)
    {
        $this->routeQueryParts = $parts;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getQueryParts()
    {
        return (array)$this->routeQueryParts;
    }

    /**
     * Generate custom entity ID if slug is not related to any real entity
     *
     * @return string
     */
    private function generateEntityId()
    {
        return sprintf('%u', crc32(json_encode($this->routeQueryParts)));
    }
}
