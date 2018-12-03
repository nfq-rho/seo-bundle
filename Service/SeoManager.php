<?php declare(strict_types=1);

/**
 * This file is part of the "NFQ Bundles" package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nfq\SeoBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Nfq\SeoBundle\Entity\SeoUrl;
use Nfq\SeoBundle\Entity\SeoInterface;
use Nfq\SeoBundle\Repository\SeoRepository;
use Nfq\SeoBundle\Exception\DuplicateException;
use Nfq\SeoBundle\Generator\SeoGeneratorManager;
use Nfq\SeoBundle\Traits\SeoCache;
use Nfq\SeoBundle\Traits\SeoConfig;
use Nfq\SeoBundle\Model\SeoSlug;
use Nfq\SeoBundle\Utils\SeoHelper;

/**
 * Class SeoManager
 * @package Nfq\SeoBundle\Service
 */
class SeoManager
{
    use SeoConfig;
    use SeoCache;

    private const CACHE_KEY_PREFIX = '__nfq_seo.by_std_hash.';

    /** @var SeoRepository */
    private $sr;

    /** @var EntityManagerInterface */
    private $em;

    /**  @var SeoGeneratorManager */
    private $sg;

    public function __construct(SeoGeneratorManager $sg, EntityManagerInterface $em)
    {
        $this->sg = $sg;
        $this->em = $em;

        $this->sr = $this->em->getRepository(SeoUrl::class);
    }

    public function getGeneratorManager(): SeoGeneratorManager
    {
        return $this->sg;
    }

    public function getRepository(): SeoRepository
    {
        return $this->sr;
    }

    public function resolveMissingUrl(array $routeParams, array $parsedStdParams): string
    {
        throw new \BadMethodCallException('Implement this method in extended class in order to use callback strategy');
    }

    public function getStdUrl(string $seoPath, string $locale): ?SeoInterface
    {
        $seoHash = SeoHelper::generateHash($seoPath);

        return $this->getRepository()->getEntityBySeoHash($seoHash, $locale);
    }

    public function exchangeInactiveSeoUrlForActive(SeoInterface $inactiveSeo): ?SeoInterface
    {
        $entity = $cacheItem = null;

        if ($this->canCache()) {
            $cacheItem = $this->getCache()->getItem(self::CACHE_KEY_PREFIX . $inactiveSeo->getStdPathHash());
            $entity = $cacheItem->get();
        }

        if (!$entity || ($cacheItem && !$cacheItem->isHit())) {
            $criteria = [
                'routeName' => $inactiveSeo->getRouteName(),
                'entityId' => $inactiveSeo->getEntityId(),
                'locale' => $inactiveSeo->getLocale(),
                'status' => SeoInterface::STATUS_OK,
            ];

            $entity = $this->getRepository()->findOneBy($criteria);

            if ($cacheItem && $entity) {
                $cacheItem
                    ->set($entity)
                    ->expiresAfter($this->getCacheTtl());

                $this->getCache()->save($cacheItem);
            }
        }

        return $entity;
    }

    /**
     * Get active SEO url for given route name and with given routeParameters
     */
    public function getActiveSeoUrl(string $routeName, array $routeParameters): ?SeoInterface
    {
        $hashData = $this->sg->getGenerator($routeName)->getHashParams($routeParameters);
        $stdHash = SeoHelper::generateHash($hashData);

        $entity = $cacheItem = null;

        if ($this->canCache()) {
            $cacheItem = $this->getCache()->getItem(self::CACHE_KEY_PREFIX . $stdHash);
            $entity = $cacheItem->get();
        }

        if (!$entity || ($cacheItem && !$cacheItem->isHit())) {
            $entity = $this->getRepository()->getEntityByStdHash($stdHash);

            if ($cacheItem && $entity) {
                $cacheItem
                    ->set($entity)
                    ->expiresAfter($this->getCacheTtl());

                $this->getCache()->save($cacheItem);
            }
        }

        return $entity;
    }

    /**
     * Create new active seo url. This method considers that every other seo url for given parameters
     * is marked as as invalidated or does not exist
     *
     * @return bool|SeoInterface
     */
    public function createSeoUrl(string $routeName, array $routeParams)
    {
        $generator = $this->sg->getGenerator($routeName);
        $hashParams = $generator->getHashParams($routeParams);

        $routeParams = array_replace_recursive($routeParams, $hashParams);

        $seoSlug = $generator->generate($routeParams);

        if (!$seoSlug instanceof SeoSlug) {
            return false;
        }

        $newSeoUrl = $this->buildEntity($seoSlug, $hashParams);

        if ($this->canCache()) {
            $cacheItem = $this->getCache()->getItem(self::CACHE_KEY_PREFIX . $newSeoUrl->getStdPathHash());

            if (null !== $entity = $cacheItem->get()) {
                return $entity;
            }
        }

        try {
            /**
             * @TODO: improve iteration so that it would not check duplicates from 1
             * for example if there are 10 similar links (some-link0-1 ... some-link-10), it would loop 10 times,
             * issuing 10 SQL queries,  before creating new URI
             */
            $iteration = 1;
            while (null !== ($existingSeoUrl = $this->getRepository()->isUnique($newSeoUrl))) {
                $this->getRepository()->makeUnique(
                    $existingSeoUrl,
                    $newSeoUrl,
                    $iteration++,
                    $this->getSlugSeparator()
                );
            }

            if ($this->canCache()) {
                $cacheItem = $this->getCache()
                    ->getItem(self::CACHE_KEY_PREFIX . $newSeoUrl->getStdPathHash())
                    ->set($newSeoUrl)
                    ->expiresAfter($this->getCacheTtl());

                $this->getCache()->save($cacheItem);
            }

            $this->getRepository()->save($newSeoUrl);
        } catch (DuplicateException $exception) {
            $this->getRepository()->handleDuplicate($newSeoUrl);
        }

        return $newSeoUrl;
    }

    private function buildEntity(SeoSlug $seoSlug, array $hashParams): SeoInterface
    {
        /** @var SeoInterface $seoUrl */
        $seoUrl = $this->getRepository()->createEntity();

        $seoUrlStr = SeoHelper::glueUrl($seoSlug, $this->getPathSeparator(), $this->getSlugSeparator());
        $seoHashStr = SeoHelper::generateHash($seoUrlStr);

        $hashParams = array_replace_recursive($hashParams, $seoSlug->getQueryParts());

        $seoUrl
            ->setStdUrl(SeoHelper::buildStdUrl($hashParams))
            ->setStdPathHash(SeoHelper::generateHash($hashParams))
            ->setSeoUrl($seoUrlStr)
            ->setSeoPathHash($seoHashStr)
            ->setEntityId($seoSlug->getEntityId())
            ->setLocale($seoSlug->getLocale())
            ->setStatus(SeoInterface::STATUS_OK)
            ->setRouteName($seoSlug->getRouteName());

        return $seoUrl;
    }
}
