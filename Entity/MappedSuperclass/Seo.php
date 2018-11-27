<?php declare(strict_types=1);

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
 * @ORM\MappedSuperclass(repositoryClass="Nfq\SeoBundle\Repository\SeoRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Seo
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer", nullable=false, options={"unsigned":true})
     */
    private $seoPathHash;

    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer", nullable=false, options={"unsigned":true})
     */
    private $stdPathHash;

    /**
     * @var string
     * @ORM\Column(length=5, nullable=false, options={"fixed":true, "collation":"ascii_bin"})
     */
    private $locale;

    /**
     * @var string
     * @ORM\Column(length=35, nullable=false, options={"fixed":true, "collation":"ascii_bin"})
     */
    private $routeName;

    /**
     * @var int
     * @ORM\Column(type="integer", nullable=false, options={"unsigned":true})
     */
    private $entityId;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=false)
     */
    private $seoUrl;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=false)
     */
    private $stdUrl;

    /**
     * @var int
     * @ORM\Column(type="smallint", nullable=false, options={"default": 1})
     */
    private $status;

    /**
     * @var \DateTimeInterface
     * @ORM\Column(type="datetime", nullable=false)
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

    public function setLocale(string $locale): SeoInterface
    {
        $this->locale = $locale;

        return $this;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function setRouteName(string $routeName): SeoInterface
    {
        $this->routeName = $routeName;

        return $this;
    }

    public function getRouteName(): string
    {
        return $this->routeName;
    }

    public function setEntityId(int $entityId): SeoInterface
    {
        $this->entityId = $entityId;

        return $this;
    }

    public function getEntityId(): int
    {
        return $this->entityId;
    }

    public function setSeoUrl(string $seoUrl): SeoInterface
    {
        $this->seoUrl = $seoUrl;

        return $this;
    }

    public function getSeoUrl(): string
    {
        return $this->seoUrl;
    }

    public function setStdUrl(string $stdUrl): SeoInterface
    {
        $this->stdUrl = $stdUrl;

        return $this;
    }

    public function getStdUrl(): string
    {
        return $this->stdUrl;
    }

    public function getParsedStdUrl(bool $decode = false): array
    {
        $url = $this->stdUrl;

        if ($decode) {
            $url = rawurldecode($this->stdUrl);
        }

        $parsed = parse_url($url);

        return $parsed === false ? [] : $parsed;
    }

    public function setStatus(int $status): SeoInterface
    {
        $this->status = $status;

        return $this;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function setSeoPathHash(string $seoPathHash): SeoInterface
    {
        $this->seoPathHash = $seoPathHash;

        return $this;
    }

    public function getSeoPathHash(): string
    {
        return $this->seoPathHash;
    }

    public function setStdPathHash(string $stdPathHash): SeoInterface
    {
        $this->stdPathHash = $stdPathHash;

        return $this;
    }

    public function getStdPathHash(): string
    {
        return $this->stdPathHash;
    }

    public function getTimestamp(): \DateTimeInterface
    {
        return $this->timestamp;
    }

    public function isOK(): bool
    {
        return $this->getStatus() === SeoInterface::STATUS_OK;
    }

    public function isInvalidated(): bool
    {
        return $this->getStatus() === SeoInterface::STATUS_REDIRECT;
    }

    public function isInvalid(): bool
    {
        return $this->getStatus() === SeoInterface::STATUS_INVALID;
    }
}
