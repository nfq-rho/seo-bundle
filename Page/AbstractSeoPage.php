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
 * Class AbstractSeoPage
 * @package Nfq\SeoBundle\Page
 */
abstract class AbstractSeoPage
{
    /**
     * @var string
     */
    protected $host;

    /**
     * @var string
     */
    protected $simpleHost;

    /**
     * @var string
     */
    protected $locale;

    /**
     * @var string
     */
    protected $titleData;

    /**
     * @var array
     */
    protected $metas;

    /**
     * @var array
     */
    protected $headAttributes;

    /**
     * @var array
     */
    protected $htmlAttributes;

    /**
     * @var array
     */
    protected $langAlternates;

    /**
     * @var array
     */
    protected $links;

    /**
     * An array of allowed query params for rels:
     *
     * @var array
     */
    protected $linkOptions;

    /**
     * @inheritdoc
     */
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
            SeoPageInterface::SEO_REL_PREV => [],
            SeoPageInterface::SEO_REL_NEXT => [],
            SeoPageInterface::SEO_REL_CANONICAL => [],
            SeoPageInterface::SEO_REL_ALTERNATE => [],
        ];

        $this->linkOptions = [];
        $this->htmlAttributes = [];
        $this->headAttributes = [];
        $this->langAlternates = [];
    }

    /**
     * @param $type
     * @return array
     */
    protected function getLinks($type)
    {
        return isset($this->links[$type]) ? $this->links[$type] : $this->links;
    }
    /**
     * @param string $type
     * @param string $uri
     * @return $this
     */
    protected function setLink($type, $uri)
    {
        if (array_key_exists($type, $this->links)) {
            $this->addLink($type, $uri, false);
        }

        return $this;
    }

    /**
     * @param string $type
     * @param array $uris
     * @return $this
     */
    protected function addLinks($type, array $uris)
    {
        if (array_key_exists($type, $this->links)) {
            foreach($uris as $key => $uri) {
                $this->addLink($type, $uri, $key);
            }
        }

        return $this;
    }

    /**
     * @param string $type
     * @param string $data
     * @param string|null|bool $index
     * @return $this
     */
    private function addLink($type, $data, $index = null)
    {
        if (is_null($index)) {
            $this->links[$type][] = $data;
        } elseif ($index === false) {
            $this->links[$type] = $data;
        } else {
            $this->links[$type][$index] = $data;
        }

        return $this;
    }

    /**
     * @param array $options
     * @return $this
     */
    public function setLinkOptions(array $options)
    {
        $this->linkOptions = $options;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getLinkOptions($option = null)
    {
        return $option && isset($this->linkOptions[$option]) ? $this->linkOptions[$option] : $this->linkOptions;
    }
}
