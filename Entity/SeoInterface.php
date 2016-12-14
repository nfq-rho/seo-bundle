<?php
/**
 * This file is part of the "NFQ Bundles" package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nfq\SeoBundle\Entity;

/**
 * Interface SeoInterface
 * @package Nfq\SeoBundle\Entity
 */
interface SeoInterface
{
    const STATUS_INVALID = 0;
    const STATUS_OK = 1;
    const STATUS_REDIRECT = 2;

    /**
     * @param string $stdUrl
     * @return $this
     */
    public function setStdUrl($stdUrl);

    /**
     * @return string
     */
    public function getStdUrl();

    /**
     * @param bool $decode
     * @return string
     */
    public function getParsedStdUrl($decode = false);
    
    /**
     * @param string $stdPathHash
     * @return $this
     */
    public function setStdPathHash($stdPathHash);

    /**
     * @return string
     */
    public function getStdPathHash();

    /**
     * @param string $seoUrl
     * @return $this
     */
    public function setSeoUrl($seoUrl);

    /**
     * @return string
     */
    public function getSeoUrl();

    /**
     * @param int $entityId
     * @return $this
     */
    public function setEntityId($entityId);

    /**
     * @return int
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
     * @param string $status
     * @return $this
     */
    public function setStatus($status);

    /**
     * @return string
     */
    public function getStatus();

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
     * @param string $seoPathHash
     * @return $this
     */
    public function setSeoPathHash($seoPathHash);

    /**
     * @return string
     */
    public function getSeoPathHash();

    /**
     * @return bool
     */
    public function isOK();

    /**
     * @return bool
     */
    public function isInvalidated();

    /**
     * @return bool
     */
    public function isInvalid();
}
