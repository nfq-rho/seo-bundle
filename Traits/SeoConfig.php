<?php declare(strict_types=1);

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
    /** @var array */
    private $config = [];

    public function setPathSeparator(string $pathSeparator): self
    {
        $this->config['path_separator'] = $pathSeparator;
        return $this;
    }

    public function getPathSeparator(): string
    {
        return $this->config['path_separator'];
    }

    public function setSlugSeparator(string $slugSeparator): self
    {
        $this->config['slug_separator'] = $slugSeparator;
        return $this;
    }

    public function getSlugSeparator(): string
    {
        return $this->config['slug_separator'];
    }

    public function setNotFoundMessage(string $message): self
    {
        $this->config['exception_message'] = $message;
        return $this;
    }

    public function getNotFoundMessage(): string
    {
        return $this->config['exception_message'];
    }

    public function setMissingUrlStrategy(string $strategy): self
    {
        $this->config['missing_url_strategy'] = $strategy;
        return $this;
    }

    public function getMissingUrlStrategy(): string
    {
        return $this->config['missing_url_strategy'];
    }
}
