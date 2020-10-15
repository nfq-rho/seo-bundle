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

    public function setCacheTtl(int $ttl): void
    {
        $this->config['cache_ttl'] = $ttl;
    }

    public function getCacheTtl(): int
    {
        return (int)$this->config['cache_ttl'];
    }

    public function setPathSeparator(string $pathSeparator): void
    {
        $this->config['path_separator'] = $pathSeparator;
    }

    public function getPathSeparator(): string
    {
        return $this->config['path_separator'];
    }

    public function setSlugSeparator(string $slugSeparator): void
    {
        $this->config['slug_separator'] = $slugSeparator;
    }

    public function getSlugSeparator(): string
    {
        return $this->config['slug_separator'];
    }

    public function setNotFoundMessage(string $message): void
    {
        $this->config['exception_message'] = $message;
    }

    public function getNotFoundMessage(): string
    {
        return $this->config['exception_message'];
    }

    public function setMissingUrlStrategy(?string $strategy): void
    {
        $this->config['missing_url_strategy'] = $strategy;
    }

    public function getMissingUrlStrategy(): ?string
    {
        return $this->config['missing_url_strategy'];
    }
}
