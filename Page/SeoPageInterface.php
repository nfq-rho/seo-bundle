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

interface SeoPageInterface
{
    /**
     * @const string
     */
    const SEO_REL_NEXT = 'next';

    /**
     * @const string
     */
    const SEO_REL_PREV = 'prev';

    /**
     * @const string
     */
    const SEO_REL_ALTERNATE = 'alternate';

    /**
     * @const string
     */
    const SEO_REL_CANONICAL = 'canonical';

    /**
     * @param string $host
     * @return $this
     */
    public function setHost($host);

    /**
     * @return string
     */
    public function getHost();

    /**
     * @param string $simpleHost
     * @return $this
     */
    public function setSimpleHost($simpleHost);

    /**
     * @return string
     */
    public function getSimpleHost();

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
     * @return string
     */
    public function getTitle();

    /**
     * @return array
     */
    public function getTitleExtras();

    /**
     * Parameter $extra usually used for passing arguments required for translation.
     * If no translatable params required, but the title still needs
     * to be translated, pass dummy ['translatable' => true].
     *
     * @param $title
     * @param array $extras
     * @return $this
     */
    public function setTitle($title, array $extras = []);

    /**
     * @return array
     */
    public function getMetas();

    /**
     * @param $type
     * @param $name
     * @param $content
     * @param array $extras
     * @return $this
     */
    public function addMeta($type, $name, $content, array $extras = []);

    /**
     * @param $name
     * @return bool
     */
    public function hasMeta($type, $name);

    /**
     * @param array $metas
     * @return $this
     */
    public function setMetas(array $metas);

    /**
     * @param array $attributes
     * @return $this
     */
    public function setHtmlAttributes(array $attributes);

    /**
     * @param $name
     * @param $value
     * @return $this
     */
    public function addHtmlAttribute($name, $value);

    /**
     * @return array
     */
    public function getHtmlAttributes();

    /**
     * @param array $attributes
     * @return $this
     */
    public function setHeadAttributes(array $attributes);

    /**
     * @param $name
     * @param $value
     * @return $this
     */
    public function addHeadAttribute($name, $value);

    /**
     * @return array
     */
    public function getHeadAttributes();

    /**
     * @param string $link
     * @return $this
     */
    public function setLinkCanonical($link);

    /**
     * @return string
     */
    public function getLinkCanonical();

    /**
     * @param string $link
     * @return $this
     */
    public function setLinkPrevPage($link);

    /**
     * @return string
     */
    public function getLinkPrevPage();

    /**
     * @param string $link
     * @return $this
     */
    public function setLinkNextPage($link);

    /**
     * @return string
     */
    public function getLinkNextPage();

    /**
     * @return array
     */
    public function getLangAlternates();

    /**
     * @param array $alternates
     * @return $this
     */
    public function setLangAlternates(array $alternates);

    /**
     * @param string|null $option
     * @return array
     */
    public function getLinkOptions($option = null);
}
