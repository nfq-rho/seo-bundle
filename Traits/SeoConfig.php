<?php
/**
 * This file is part of the "NFQ Bundles" package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nfq\SeoBundle\Traits;

/**
 * Class SeoConfig
 * @package Nfq\SeoBundle\Traits
 */
trait SeoConfig
{
    /**
     * @var array
     */
    private $config = [];

    /**
     * @param $pathSeparator
     * @return $this
     */
    public function setPathSeparator($pathSeparator)
    {
        $this->config['path_separator'] = $pathSeparator;
        return $this;
    }

    /**
     * @return string
     */
    public function getPathSeparator()
    {
        return $this->config['path_separator'];
    }

    /**
     * @param string $slugSeparator
     * @return $this
     */
    public function setSlugSeparator($slugSeparator)
    {
        $this->config['slug_separator'] = $slugSeparator;
        return $this;
    }

    /**
     * @return string
     */
    public function getSlugSeparator()
    {
        return $this->config['slug_separator'];
    }

    /**
     * @param string $message
     * @return $this
     */
    public function setNotFoundMessage($message)
    {
        $this->config['exception_message'] = $message;
        return $this;
    }

    /**
     * @return string
     */
    public function getNotFoundMessage()
    {
        return $this->config['exception_message'];
    }

    /**
     * @param string $strategy
     * @return $this
     */
    public function setMissingUrlStrategy($strategy)
    {
        $this->config['missing_url_strategy'] = $strategy;
        return $this;
    }

    /**
     * @return string
     */
    public function getMissingUrlStrategy()
    {
        return $this->config['missing_url_strategy'];
    }
}
