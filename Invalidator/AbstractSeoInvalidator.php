<?php
/**
 * This file is part of the "NFQ Bundles" package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nfq\SeoBundle\Invalidator;

use Doctrine\ORM\EntityManagerInterface;
use Nfq\SeoBundle\Entity\SeoInterface;
use Nfq\SeoBundle\Invalidator\Object\InvalidationObjectInterface;

/**
 * Class AbstractSeoInvalidator
 * @package Nfq\SeoBundle\Invalidator
 */
abstract class AbstractSeoInvalidator implements SeoInvalidatorInterface
{
    /**
     * @var string
     */
    private $currentRouteName;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @inheritdoc
     */
    public function getRouteName()
    {
        return $this->currentRouteName;
    }

    /**
     * @inheritdoc
     */
    public function setRouteName($routeName)
    {
        $this->currentRouteName = $routeName;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setEntityManager(EntityManagerInterface $em)
    {
        $this->em = $em;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getEntityManager()
    {
        return $this->em;
    }

    /**
     * @param InvalidationObjectInterface $invalidationObject
     * @return $this
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function executeRemoval(InvalidationObjectInterface $invalidationObject)
    {
        $statement = 'DELETE FROM seo_urls WHERE route_name = :routeName AND entity_id = :entityId';

        $whereParams = [
            'routeName' => $this->getRouteName(),
            'entityId' => $invalidationObject->getEntity()->getId(),
        ];

        $this->executeStatement($statement, $whereParams);

        return $this;
    }

    /**
     * @param InvalidationObjectInterface $invalidationObject
     * @return $this
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function executeInvalidation(InvalidationObjectInterface $invalidationObject)
    {
        if (!$invalidationObject->hasChanges()) {
            return $this;
        }

        $queryString = $this->getInvalidationQueryString($invalidationObject);

        $whereParams = array_merge([
            'route_name' => $this->getRouteName(),
            'locale' => $invalidationObject->getLocale(),
        ], $invalidationObject->getWhereParams(), [
            'active_status' => SeoInterface::STATUS_OK,
            'target_status' => $invalidationObject->getInvalidationStatus()
        ]);
        
        $whereParams = array_filter($whereParams, function ($value) {
            return $value !== null;
        });

        $this->executeStatement($queryString, $whereParams);

        return $this;
    }

    /**
     * @param string $queryString
     * @param array $params
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function executeStatement($queryString, $params)
    {
        $stmt = $this->getEntityManager()->getConnection()->prepare($queryString);
        $stmt->execute($params);
        $stmt->closeCursor();
    }

    /**
     * Builds invalidation query based on given invalidation object. The invalidation is executed only
     * for active urls.
     *
     * @param InvalidationObjectInterface $invalidationObject
     * @return string
     * @throws \Exception
     */
    private function getInvalidationQueryString(InvalidationObjectInterface $invalidationObject)
    {
        $query = 'UPDATE seo_urls su ';

        if ($joinPart = $invalidationObject->getJoinPart()) {
            $query .= sprintf('JOIN %s ', $joinPart);
        }

        $query .= 'SET su.status = :target_status WHERE su.status = :active_status AND su.route_name = :route_name';

        if (null !== $invalidationObject->getLocale()) {
            $query .= ' AND su.locale = :locale';
        }

        if ($wherePart = $invalidationObject->getWherePart()) {
            $query .= sprintf(' AND %s', $wherePart);
        }

        return $query;
    }
}
