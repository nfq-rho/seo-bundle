<?php
/**
 * This file is part of the "NFQ Bundles" package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nfq\SeoBundle\Entity\MappedSuperclass;

use Doctrine\ORM\Mapping as ORM;
use Nfq\SeoBundle\Entity\SeoInterface;

/**
 * @ORM\MappedSuperclass(repositoryClass="Nfq\SeoBundle\Entity\SeoRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Seo
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(name="seo_path_hash", type="integer", nullable=false, options={"unsigned":true})
     */
    private $seoPathHash;

    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(name="std_path_hash", type="integer", nullable=false, options={"unsigned":true})
     */
    private $stdPathHash;

    /**
     * @var string
     *
     * @ORM\Column(name="locale", length=5, nullable=false, options={"fixed":true, "collation":"ascii_bin"})
     */
    private $locale;

    /**
     * @var string
     *
     * @ORM\Column(name="route_name", length=35, nullable=false, options={"fixed":true, "collation":"ascii_bin"})
     */
    private $routeName;

    /**
     * @var integer
     *
     * @ORM\Column(name="entity_id", type="integer", nullable=false, options={"unsigned":true})
     */
    private $entityId;

    /**
     * @var string
     *
     * @ORM\Column(name="seo_url", type="string", nullable=false)
     */
    private $seoUrl;

    /**
     * @var string
     *
     * @ORM\Column(name="std_url", type="string", nullable=false)
     */
    private $stdUrl;

    /**
     * @var int
     *
     * @ORM\Column(name="status", type="smallint", nullable=false, options={"default": 1})
     */
    private $status;

    /**
     * @var
     * @ORM\Column(name="timestamp", type="datetime", nullable=false)
     */
    private $timestamp;

    /**
     * @ORM\PreUpdate
     * @ORM\PrePersist
     */
    public function onPrePersist()
    {
        $this->timestamp = new \DateTime();
    }

    /**
     * Set locale
     *
     * @param string $locale
     * @return Seo
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * Get locale
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Set type
     *
     * @param string $routeName
     * @return Seo
     */
    public function setRouteName($routeName)
    {
        $this->routeName = $routeName;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getRouteName()
    {
        return $this->routeName;
    }

    /**
     * Set entityId
     *
     * @param integer $entityId
     * @return Seo
     */
    public function setEntityId($entityId)
    {
        $this->entityId = $entityId;

        return $this;
    }

    /**
     * Get entityId
     *
     * @return integer
     */
    public function getEntityId()
    {
        return $this->entityId;
    }

    /**
     * Set seoUrl
     *
     * @param string $seoUrl
     * @return Seo
     */
    public function setSeoUrl($seoUrl)
    {
        $this->seoUrl = $seoUrl;

        return $this;
    }

    /**
     * Get seoUrl
     *
     * @return string
     */
    public function getSeoUrl()
    {
        return $this->seoUrl;
    }

    /**
     * Set stdUrl
     *
     * @param string $stdUrl
     * @return Seo
     */
    public function setStdUrl($stdUrl)
    {
        $this->stdUrl = $stdUrl;

        return $this;
    }

    /**
     * Get stdUrl
     *
     * @return string
     */
    public function getStdUrl()
    {
        return $this->stdUrl;
    }

    /**
     * @return array
     */
    public function getParsedStdUrl($decode = false)
    {
        $url = $this->stdUrl;

        if ($decode) {
            $url = rawurldecode($this->stdUrl);
        }

        return parse_url($url);
    }

    /**
     * Set status
     *
     * @param \bool $status
     * @return Seo
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return \bool
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set seoPathHash
     *
     * @param string $seoPathHash
     * @return Seo
     */
    public function setSeoPathHash($seoPathHash)
    {
        $this->seoPathHash = $seoPathHash;

        return $this;
    }

    /**
     * Get seoPathHash
     *
     * @return string
     */
    public function getSeoPathHash()
    {
        return $this->seoPathHash;
    }

    /**
     * Set stdPathHash
     *
     * @param string $stdPathHash
     * @return Seo
     */
    public function setStdPathHash($stdPathHash)
    {
        $this->stdPathHash = $stdPathHash;

        return $this;
    }

    /**
     * Get stdPathHash
     *
     * @return string
     */
    public function getStdPathHash()
    {
        return $this->stdPathHash;
    }

    /**
     * @return \DateTime
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * {@inheritdoc}
     */
    public function isOK()
    {
        return $this->getStatus() == SeoInterface::STATUS_OK;
    }

    /**
     * {@inheritdoc}
     */
    public function isInvalidated()
    {
        return $this->getStatus() == SeoInterface::STATUS_REDIRECT;
    }

    /**
     * {@inheritdoc}
     */
    public function isInvalid()
    {
        return $this->getStatus() == SeoInterface::STATUS_INVALID;
    }
}
