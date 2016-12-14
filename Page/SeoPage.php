<?php
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
 * Class SeoPage
 * @package Nfq\SeoBundle\Page
 */
class SeoPage extends AbstractSeoPage implements SeoPageInterface
{
    /**
     * @inheritdoc
     */
    public function setHost($host)
    {
        $this->host = $host;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @inheritdoc
     */
    public function getSimpleHost()
    {
        return $this->simpleHost;
    }

    /**
     * @inheritdoc
     */
    public function setSimpleHost($simpleHost)
    {
        $this->simpleHost = $simpleHost;
        return $this;
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
    public function getTitle()
    {
        return $this->titleData['title'];
    }

    /**
     * @inheritdoc
     */
    public function getTitleExtras()
    {
        return $this->titleData['extras'];
    }

    /**
     * @inheritdoc
     */
    public function setTitle($title, array $extras = [])
    {
        $this->titleData = [
            'title' => $title,
            'extras' => $extras
        ];
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getMetas()
    {
        return $this->metas;
    }

    /**
     * @inheritdoc
     */
    public function addMeta($type, $name, $content, array $extras = [])
    {
        if (!isset($this->metas[$type])) {
            $this->metas[$type] = [];
        }
        $this->metas[$type][$name] = [$content, $extras];
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function hasMeta($type, $name)
    {
        return isset($this->metas[$type][$name]);
    }

    /**
     * @inheritdoc
     */
    public function setMetas(array $metadatas)
    {
        $this->metas = [];

        foreach ($metadatas as $type => $metas) {
            if (!is_array($metas)) {
                throw new \RuntimeException('$metas must be an array');
            }

            foreach ($metas as $name => $meta) {
                list($content, $extras) = $this->normalize($meta, $name, $type);
                $this->addMeta($type, $name, $content, $extras);
            }
        }
        return $this;
    }

    /**
     * @param mixed $meta
     * @return array
     */
    private function normalize($meta, &$name, $type)
    {
        if ($type == 'charset') {
            $name = str_replace('_', '-', $name);
        }

        if ($meta === false || is_null($meta)) {
            return [false, []];
        }

        //Meta with empty value
        if ($meta === true) {
            return [null, []];
        }

        if (is_array($meta)) {
            $value = $meta['value'];

            unset($meta['value']);
            $extras = $meta;

            return [$value, $extras];
        }

        if (is_string($meta)) {
            return [$meta, ['translatable' => true]];
        }

        return $meta;
    }

    /**
     * @inheritdoc
     */
    public function setHtmlAttributes(array $attributes)
    {
        $this->htmlAttributes = $attributes;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function addHtmlAttribute($name, $value)
    {
        $this->htmlAttributes[$name] = $value;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getHtmlAttributes()
    {
        return $this->htmlAttributes;
    }

    /**
     * @inheritdoc
     */
    public function setHeadAttributes(array $attributes)
    {
        $this->headAttributes = $attributes;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getHeadAttributes()
    {
        return $this->headAttributes;
    }

    /**
     * @inheritdoc
     */
    public function addHeadAttribute($name, $value)
    {
        $this->headAttributes[$name] = $value;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setLinkNextPage($link)
    {
        $this->setLink(SeoPageInterface::SEO_REL_NEXT, $link);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getLinkNextPage()
    {
        return $this->getLinks(SeoPageInterface::SEO_REL_NEXT);
    }

    /**
     * @inheritdoc
     */
    public function setLinkPrevPage($link)
    {
        $this->setLink(SeoPageInterface::SEO_REL_PREV, $link);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getLinkPrevPage()
    {
        return $this->getLinks(SeoPageInterface::SEO_REL_PREV);
    }

    /**
     * @inheritdoc
     */
    public function setLinkCanonical($link)
    {
        $this->setLink(SeoPageInterface::SEO_REL_CANONICAL, $link);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getLinkCanonical()
    {
        $canonical = $this->getLinks(SeoPageInterface::SEO_REL_CANONICAL);
        return (string)(is_array($canonical) ? array_pop($canonical) : $canonical);
    }

    /**
     * {@inheritdoc}
     */
    public function setLangAlternates(array $langAlternates)
    {
        $this->addLinks(SeoPageInterface::SEO_REL_ALTERNATE, $langAlternates);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getLangAlternates()
    {
        return $this->getLinks(SeoPageInterface::SEO_REL_ALTERNATE);
    }
}
