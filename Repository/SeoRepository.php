<?php declare(strict_types=1);

/**
 * This file is part of the "NFQ Bundles" package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nfq\SeoBundle\Repository;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\QueryBuilder;
use Nfq\AdminBundle\Repository\ServiceEntityRepository;
use Nfq\SeoBundle\Entity\SeoUrl;
use Nfq\SeoBundle\Entity\SeoInterface;
use Nfq\SeoBundle\Exception\DuplicateException;
use Nfq\SeoBundle\Utils\SeoHelper;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Class SeoRepository
 * @package Nfq\SeoBundle\Entity
 */
class SeoRepository extends ServiceEntityRepository
{
    /** @var string */
    protected $alias = 'seo';

    /** @var string */
    protected $entityClass = SeoUrl::class;

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, $this->entityClass);
    }

    public function getAlias(): string
    {
        return $this->alias;
    }

    public function getSimpleQueryBuilder(): QueryBuilder
    {
        $qb = $this->_em->createQueryBuilder()
            ->select($this->getAlias())
            ->from($this->getEntityName(), $this->getAlias());

        return $qb;
    }

    public function getAlternatesArray(string $routeName, int $entityId, string $requestLocale): array
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

    public function getEntityBySeoHash(string $seoHash, string $locale): ?SeoInterface
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
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getEntityByStdHash(string $stdHash): ?SeoInterface
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
     * @throws DuplicateException
     * @throws NonUniqueResultException
     */
    public function isUnique(SeoInterface $entity): ?SeoInterface
    {
        $query = $this->createQueryBuilder('su')
            ->where(
                'su.seoPathHash = :seoPathHash AND su.status = :status AND su.locale = :locale AND su.seoUrl = :seoUrl'
            )
            ->setParameters([
                'seoPathHash' => $entity->getSeoPathHash(),
                'locale' => $entity->getLocale(),
                'status' => SeoInterface::STATUS_OK,
                'seoUrl' => $entity->getSeoUrl(),
            ])
            ->setMaxResults(1)
            ->getQuery();

        $query->useResultCache(false)->expireResultCache(true);

        /** @var SeoUrl $existingUrl */
        $existingUrl = $query->getOneOrNullResult();

        //Check if we're not generating seo duplicate for entity with different std
        if ($existingUrl
            && $existingUrl->getEntityId() === $entity->getEntityId()
            && $existingUrl->getRouteName() === $entity->getRouteName()
        ) {
            throw new DuplicateException();
        }

        return $existingUrl;
    }

    public function makeUnique(
        SeoInterface $seoUrlExisting,
        SeoInterface $seoUrlNew,
        int $iteration,
        string $slugSeparator
    ): void {
        $pattern = '~-(?P<uid>\d+)$~';
        $match = [];
        $isMatch = preg_match($pattern, $seoUrlExisting->getSeoUrl(), $match, PREG_OFFSET_CAPTURE);

        $currentCount = $isMatch ? $match['uid'][0] : 1;

        // $currentCount can not be higher than iteration, if it is it means that
        // $currentCount is a random matched number which is in entity title
        if ($currentCount > $iteration) {
            $currentCount = $iteration;
            $match = [];
        }

        $nextCount = $currentCount + 1;
        $seoUrl = $seoUrlNew->getSeoUrl();

        $seoUrl = empty($match)
            ? $seoUrl . $slugSeparator . $nextCount
            : substr_replace($seoUrl, $slugSeparator . $nextCount, $match[0][1]);

        $seoUrlNew->setSeoUrl($seoUrl);
        $seoUrlNew->setSeoPathHash((int)SeoHelper::generateHash($seoUrlNew->getSeoUrl()));
    }

    public function save(SeoInterface $entity): int
    {
        $sql = <<<SQL
INSERT INTO seo_url (
  `seo_path_hash`, 
  `std_path_hash`,
  `locale`, 
  `route_name`, 
  `entity_id`, 
  `seo_url`, 
  `std_url`, 
  `status`, 
  `timestamp`
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
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

    public function handleDuplicate(SeoInterface $entity): void
    {
        $sql = <<<SQL
UPDATE seo_url 
SET
  `std_path_hash` = ?, 
  `std_url` = ?, 
  `status` = ? 
WHERE `seo_path_hash` = ? 
  AND `route_name` = ? 
  AND `entity_id` = ? 
  AND `locale` = ?
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
