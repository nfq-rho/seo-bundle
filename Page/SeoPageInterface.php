<?php declare(strict_types=1);

/**
 * This file is part of the "NFQ Bundles" package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nfq\SeoBundle\Page;

/**
 * Interface SeoPageInterface
 * @package Nfq\SeoBundle\Page
 */
interface SeoPageInterface
{
    public const SEO_REL_NEXT = 'next';
    public const SEO_REL_PREV = 'prev';
    public const SEO_REL_ALTERNATE = 'alternate';
    public const SEO_REL_CANONICAL = 'canonical';

    public function setHost(string $host): SeoPageInterface;

    public function getHost(): ?string;

    public function setSimpleHost(string $simpleHost): SeoPageInterface;

    public function getSimpleHost(): ?string;

    public function setLocale(string $locale): SeoPageInterface;

    public function getLocale(): ?string;

    public function getTitle(): string;

    public function getTitleExtras(): array;

    /**
     * Parameter $extra usually used for passing arguments required for translation.
     * If no translatable params required, but the title still needs
     * to be translated, pass dummy ['translatable' => true].
     */
    public function setTitle(string $title, array $extras = []): SeoPageInterface;

    public function getMetas(): array;

    public function addMeta(string $type, string $name, $content, array $extras = []): SeoPageInterface;

    public function hasMeta(string $type, string $name): bool;

    public function setMetas(array $metas): SeoPageInterface;

    public function setHtmlAttributes(array $attributes): SeoPageInterface;

    public function addHtmlAttribute(string $name, $value): SeoPageInterface;

    public function getHtmlAttributes(): array;

    public function setHeadAttributes(array $attributes): SeoPageInterface;

    public function addHeadAttribute(string $name, $value): SeoPageInterface;

    public function getHeadAttributes(): array;

    public function setLinkCanonical(string $link): SeoPageInterface;

    public function getLinkCanonical(): string;

    public function setLinkPrevPage(string $link): SeoPageInterface;

    public function getLinkPrevPage(): string;

    public function setLinkNextPage(string $link): SeoPageInterface;

    public function getLinkNextPage(): string;

    public function getLangAlternates(): array;

    public function setLangAlternates(array $alternates): SeoPageInterface;

    public function getLinkOptions(string $option = null): array;
}
