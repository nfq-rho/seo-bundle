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

use Nfq\SeoBundle\Utils\SeoHelper;

/**
 * Class SeoPage
 * @package Nfq\SeoBundle\Page
 */
class SeoPage implements SeoPageInterface
{
    /** @var string */
    protected $host;

    /**  @var string */
    protected $simpleHost;

    /** @var string */
    protected $locale;

    /** @var string */
    protected $titleData;

    /** @var array */
    protected $metas;

    /** @var array */
    protected $headAttributes;

    /** @var array */
    protected $htmlAttributes;

    /** @var array */
    protected $langAlternates;

    /** @var array */
    protected $links;

    /**
     * An array of allowed query params for rels:
     *
     * @var array
     */
    protected $linkOptions;

    public function __construct()
    {
        $this->titleData = [
            'title' => '',
            'extras' => [],
        ];

        $this->metas = [
            'http-equiv' => [],
            'name' => [],
            'schema' => [],
            'charset' => [],
            'property' => [],
        ];

        $this->links = [
            self::SEO_REL_PREV => [],
            self::SEO_REL_NEXT => [],
            self::SEO_REL_CANONICAL => [],
            self::SEO_REL_ALTERNATE => [],
        ];

        $this->linkOptions = [];
        $this->htmlAttributes = [];
        $this->headAttributes = [];
        $this->langAlternates = [];
    }

    public function setLinkOptions(array $options): SeoPageInterface
    {
        $this->linkOptions = $options;
        return $this;
    }

    public function getLinkOptions(string $option = null): array
    {
        return $option && isset($this->linkOptions[$option]) ? $this->linkOptions[$option] : $this->linkOptions;
    }

    public function setHost(string $host): SeoPageInterface
    {
        $this->host = $host;
        return $this;
    }

    public function getHost(): ?string
    {
        return $this->host;
    }

    public function setSimpleHost(string $simpleHost): SeoPageInterface
    {
        $this->simpleHost = $simpleHost;
        return $this;
    }

    public function getSimpleHost(): ?string
    {
        return $this->simpleHost;
    }

    public function setLocale(string $locale): SeoPageInterface
    {
        $this->locale = $locale;
        return $this;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function setTitle(string $title, array $extras = []): SeoPageInterface
    {
        $this->titleData = [
            'title' => $title,
            'extras' => $extras
        ];
        return $this;
    }

    public function getTitle(): string
    {
        return $this->titleData['title'];
    }

    public function getTitleExtras(): array
    {
        return $this->titleData['extras'];
    }

    public function getMetas(): array
    {
        return $this->metas;
    }

    public function addMeta(string $type, string $name, $content, array $extras = []): SeoPageInterface
    {
        $hasMeta = isset($this->metas[$type][$name]);

        if (null === $content) {
            if ($hasMeta) {
                unset($this->metas[$type][$name]);
            }

            return $this;
        }

        if (!isset($this->metas[$type])) {
            $this->metas[$type] = [];
        }

        $this->metas[$type][$name] = [$content, $extras];

        return $this;
    }

    public function hasMeta(string $type, string $name): bool
    {
        return isset($this->metas[$type][$name]);
    }

    public function setMetas(array $metadatas): SeoPageInterface
    {
        $this->metas = [];

        foreach ($metadatas as $type => $metas) {
            if (!\is_array($metas)) {
                throw new \RuntimeException('$metas must be an array');
            }

            foreach ($metas as $name => $meta) {
                [$content, $extras] = $this->normalize($meta, $name, $type);
                $this->addMeta($type, $name, $content, $extras);
            }
        }
        return $this;
    }

    public function setHtmlAttributes(array $attributes): SeoPageInterface
    {
        $this->htmlAttributes = $attributes;
        return $this;
    }

    public function addHtmlAttribute(string $name, $value): SeoPageInterface
    {
        $this->htmlAttributes[$name] = $value;
        return $this;
    }

    public function getHtmlAttributes(): array
    {
        return $this->htmlAttributes;
    }

    public function setHeadAttributes(array $attributes): SeoPageInterface
    {
        $this->headAttributes = $attributes;
        return $this;
    }

    public function getHeadAttributes(): array
    {
        return $this->headAttributes;
    }

    public function addHeadAttribute(string $name, $value): SeoPageInterface
    {
        $this->headAttributes[$name] = $value;
        return $this;
    }

    public function setLinkNextPage(string $link): SeoPageInterface
    {
        $this->setLink(self::SEO_REL_NEXT, $link);
        return $this;
    }

    public function getLinkNextPage(): string
    {
        $link = $this->getLinks(self::SEO_REL_NEXT);
        return \is_array($link) ? (string)array_pop($link) : (string)$link;
    }

    public function setLinkPrevPage(string $link): SeoPageInterface
    {
        $this->setLink(self::SEO_REL_PREV, $link);
        return $this;
    }

    public function getLinkPrevPage(): string
    {
        $link = $this->getLinks(self::SEO_REL_PREV);
        return \is_array($link) ? (string)array_pop($link) : (string)$link;
    }

    public function setLinkCanonical(string $link): SeoPageInterface
    {
        $this->setLink(self::SEO_REL_CANONICAL, $link);
        return $this;
    }

    public function getLinkCanonical(): string
    {
        $link = $this->getLinks(self::SEO_REL_CANONICAL);
        return \is_array($link) ? (string)array_pop($link) : (string)$link;
    }

    public function setLangAlternates(array $langAlternates): SeoPageInterface
    {
        $this->addLinks(self::SEO_REL_ALTERNATE, $langAlternates);
        return $this;
    }

    public function getLangAlternates(): array
    {
        return $this->getLinks(self::SEO_REL_ALTERNATE);
    }

    public function formatCanonicalUrl(string $uri): string
    {
        $allowedQueryParams = $this->getLinkOptions('allowed_canonical_parameters');
        $parsedQueryString = parse_url(rawurldecode($uri), PHP_URL_QUERY);

        if (!empty($allowedQueryParams) && !empty($parsedQueryString)) {
            $flippedParams = array_flip($allowedQueryParams);

            parse_str($parsedQueryString, $parsedQueryStringArr);
            $allowedQueryStringArr = array_intersect_key($parsedQueryStringArr, $flippedParams);

            [$uriPath,] = explode('?', $uri, 2);

            return SeoHelper::getUri($uriPath, $allowedQueryStringArr);
        }

        return $uri;
    }

    /**
     * @param mixed $meta
     * @return mixed
     */
    private function normalize($meta, &$name, string $type)
    {
        if ($type === 'charset') {
            $name = str_replace('_', '-', $name);
        }

        if ($meta === false || null === $meta) {
            return [false, []];
        }

        //Meta with empty value
        if ($meta === true) {
            return [null, []];
        }

        if (\is_array($meta)) {
            $value = $meta['value'];

            unset($meta['value']);
            $extras = $meta;

            return [$value, $extras];
        }

        if (\is_string($meta)) {
            return [$meta, ['trans' => true]];
        }

        return $meta;
    }

    private function getLinks(string $type): array
    {
        return $this->links[$type] ?? $this->links;
    }

    private function setLink(string $type, string $uri): void
    {
        if (\array_key_exists($type, $this->links)) {
            $this->addLink($type, $uri);
        }
    }

    private function addLinks(string $type, array $uris): void
    {
        if (\array_key_exists($type, $this->links)) {
            foreach ($uris as $key => $uri) {
                $this->addLink($type, $uri, $key);
            }
        }
    }

    /**
     * @param mixed $data
     * @param mixed $index
     */
    private function addLink(string $type, $data, ?string $index = null): void
    {
        if (null === $index) {
            $this->links[$type][] = $data;
        } else {
            $this->links[$type][$index] = $data;
        }
    }
}
