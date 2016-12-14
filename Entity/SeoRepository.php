<?php
/**
 * This file is part of the "NFQ Bundles" package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nfq\SeoBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\QueryBuilder;
use Nfq\SeoBundle\Exception\DuplicateException;
use Nfq\SeoBundle\Utils\SeoHelper;

/**
 * Class SeoRepository
 * @package Nfq\SeoBundle\Entity
 */
class SeoRepository extends EntityRepository
{
    /**
     * @var string
     */
    protected $alias = 'seo';

    /**
     * @param string $alias
     */
    public function setAlias($alias)
    {
        $this->alias = $alias;
    }

    /**
     * @return string
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * @return SeoInterface
     */
    public function createNew()
    {
        $class = $this->getClassName();

        return new $class();
    }

    /**
     * @return QueryBuilder
     */
    public function getSimpleQueryBuilder()
    {
        $qb = $this->_em->createQueryBuilder()
            ->select($this->getAlias())
            ->from($this->getEntityName(), $this->getAlias());

        return $qb;
    }

    /**
     * @param string $routeName
     * @param int $entityId
     * @param string $requestLocale
     * @return array
     */
    public function getAlternatesArray($routeName, $entityId, $requestLocale)
    {
        $qb = $this->getSimpleQueryBuilder();
        $qb->select(['seo.locale', 'seo.seoUrl'])
            ->where('seo.entityId = :entity_id')
            ->andWhere('seo.routeName = :route_name')
            ->andWhere('seo.status = :status')
            ->andWhere('seo.locale <> :current_locale')
            ->setParameters([
                'status' => SeoInterface::STATUS_OK,
                'route_name' => $routeName,
                'entity_id' => $entityId,
                'current_locale' => $requestLocale,
            ]);

        return $qb->getQuery()->getArrayResult();
    }

    /**
     * @param string $seoHash
     * @param string $locale
     * @return Seo|null
     */
    public function getEntityBySeoHash($seoHash, $locale)
    {
        try {
            $qb = $this->getSimpleQueryBuilder();
            $qb
                ->where('seo.seoPathHash = :seoHash AND seo.locale = :locale')
                ->orderBy('seo.status', 'ASC')
                ->setMaxResults(1)
                ->setParameters([
                    'locale' => $locale,
                    'seoHash' => $seoHash,
                ]);

            $entity = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $ex) {
            return null;
        }

        return $entity;
    }

    /**
     * Only one URL with status `OK` can exist, but there might be more than one url with status `INVALIDATED`
     *
     * @param string $stdHash
     * @return Seo|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getEntityByStdHash($stdHash)
    {
        $qb = $this->createQueryBuilder('su')
            ->where('su.stdPathHash = :slugHash AND su.status = :status')
            ->setParameters(['slugHash' => $stdHash, 'status' => SeoInterface::STATUS_OK]);

        $query = $qb->getQuery();

        return $query->getOneOrNullResult();
    }

    /**
     * Check if SEO entity is unique.
     *
     * @param SeoInterface $entity
     * @return SeoInterface|null
     * @throws DuplicateException
     * @throws NonUniqueResultException
     */
    public function isUnique(SeoInterface $entity)
    {
        $query = $this->createQueryBuilder('su')
            ->where('su.seoPathHash = :seoPathHash and su.status = :status AND su.locale = :locale AND su.seoUrl = :seoUrl')
            ->setParameters([
                'seoPathHash' => $entity->getSeoPathHash(),
                'locale' => $entity->getLocale(),
                'status' => SeoInterface::STATUS_OK,
                'seoUrl' => $entity->getSeoUrl(),
            ])
            ->setMaxResults(1)
            ->getQuery();

        $query->useResultCache(false)->expireResultCache(true);

        /** @var Seo $existingUrl */
        $existingUrl = $query->getOneOrNullResult();

        //Check if we're not generating seo duplicate for entity with different std
        if ($existingUrl && $existingUrl->getEntityId() == $entity->getEntityId()
            && $existingUrl->getRouteName() == $entity->getRouteName()
        ) {
            throw new DuplicateException();
        }

        return $existingUrl;
    }

    /**
     * @param SeoInterface $seoUrlExisting
     * @param SeoInterface $seoUrlNew
     * @param int $iteration
     * @param string $slugSeparator
     */
    public function makeUnique(SeoInterface $seoUrlExisting, SeoInterface $seoUrlNew, $iteration, $slugSeparator)
    {
        $pattern = '~-(?P<uid>[0-9]+)$~';
        $match = [];
        $isMatch = preg_match($pattern, $seoUrlExisting->getSeoUrl(), $match, PREG_OFFSET_CAPTURE);

        $currentCount = ($isMatch) ? $match['uid'][0] : 1;

        // $currentCount can not be higher than iteration, if it is it means that
        // $currentCount is a random matched number which is in entity title
        if ($currentCount > $iteration) {
            $currentCount = $iteration;
            $match = [];
        }

        $nextCount = (int)($currentCount + 1);
        $seoUrl = $seoUrlNew->getSeoUrl();

        $seoUrl = (empty($match))
            ? $seoUrl . $slugSeparator . $nextCount
            : substr_replace($seoUrl, $slugSeparator . $nextCount, $match[0][1]);

        $seoUrlNew->setSeoUrl($seoUrl);
        $seoUrlNew->setSeoPathHash(SeoHelper::generateHash($seoUrlNew->getSeoUrl()));
    }

    /**
     * @param SeoInterface $entity
     * @return int
     */
    public function save(SeoInterface $entity)
    {
        $sql = <<<SQL
INSERT INTO seo_urls (`seo_path_hash`, `std_path_hash`, `locale`, `route_name`, `entity_id`, `seo_url`, `std_url`, `status`, `timestamp`) 
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
ON DUPLICATE KEY UPDATE `status` = ?, `timestamp` = NOW()
SQL;

        $params = [
            $entity->getSeoPathHash(),
            $entity->getStdPathHash(),
            $entity->getLocale(),
            $entity->getRouteName(),
            $entity->getEntityId(),
            $entity->getSeoUrl(),
            $entity->getStdUrl(),
            $entity->getStatus(),
            (new \DateTime())->format('Y-m-d H:i:s'),
            SeoInterface::STATUS_OK,
        ];

        $stmt = $this->_em->getConnection()->executeQuery($sql, $params);
        $stmt->execute();

        /**
         * 1 - new entity was inserted
         * 2 - entity was revived
         */
        return $stmt->rowCount();
    }

    /**
     * @param SeoInterface $entity
     * @throws \Doctrine\DBAL\DBALException
     */
    public function handleDuplicate(SeoInterface $entity)
    {
        $sql = <<<SQL
UPDATE seo_urls SET `std_path_hash` = ?, `std_url` = ?, `status` = ? WHERE `seo_path_hash` = ? AND `route_name` = ? AND `entity_id` = ? AND `locale` = ?
SQL;

        $params = [
            $entity->getStdPathHash(),
            $entity->getStdUrl(),
            SeoInterface::STATUS_OK,
            $entity->getSeoPathHash(),
            $entity->getRouteName(),
            $entity->getEntityId(),
            $entity->getLocale(),
        ];

        $stmt = $this->_em->getConnection()->executeQuery($sql, $params);
        $stmt->execute();
    }
}
