<?php declare(strict_types=1);

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
    public const STATUS_INVALID = 0;
    public const STATUS_OK = 1;
    public const STATUS_REDIRECT = 2;

    public function setStdUrl(string $stdUrl): SeoInterface;

    public function getStdUrl(): string;

    public function getParsedStdUrl(bool $decode = false): array;
    
    public function setStdPathHash(int $stdPathHash): SeoInterface;

    public function getStdPathHash(): int;

    public function setSeoUrl(string $seoUrl): SeoInterface;

    public function getSeoUrl(): string;

    public function setEntityId(int $entityId): SeoInterface;

    public function getEntityId(): int;

    public function setLocale(string $locale): SeoInterface;

    public function getLocale(): string;

    public function setStatus(int $status): SeoInterface;

    public function getStatus(): int;

    public function setRouteName(string $routeName): SeoInterface;

    public function getRouteName(): string;

    public function setSeoPathHash(int $seoPathHash): SeoInterface;

    public function getSeoPathHash(): int;

    public function isOK(): bool;

    public function isInvalidated(): bool;

    public function isInvalid(): bool;
}
