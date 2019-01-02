<?php declare(strict_types=1);

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
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpKernel\Kernel;

/**
 * Class AbstractSeoInvalidator
 * @package Nfq\SeoBundle\Invalidator
 */
abstract class AbstractSeoInvalidatorBase implements SeoInvalidatorInterface
{
    /** @var string */
    private $currentRouteName;

    /** @var ContainerInterface */
    private $locator;

    public function __construct(ContainerInterface $locator)
    {
        $this->locator = $locator;
    }

    public static function getSubscribedServices(): array
    {
        return [
            'doctrine' => EntityManagerInterface::class,
        ];
    }

    public function getRouteName(): string
    {
        return $this->currentRouteName;
    }

    public function setRouteName(string $routeName): SeoInvalidatorInterface
    {
        $this->currentRouteName = $routeName;
        return $this;
    }

    public function getEntityManager(): EntityManagerInterface
    {
        return $this->locator->get('doctrine');
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function executeRemoval(InvalidationObjectInterface $invalidationObject): void
    {
        $statement = 'DELETE FROM seo_url WHERE route_name = :routeName AND entity_id = :entityId';

        $whereParams = [
            'routeName' => $this->getRouteName(),
            'entityId' => $invalidationObject->getEntity()->getId(),
        ];

        $this->executeStatement($statement, $whereParams);
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function executeInvalidation(InvalidationObjectInterface $invalidationObject): void
    {
        if (!$invalidationObject->hasChanges()) {
            return;
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
    }

    /**
     * @param string[] $params
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function executeStatement(string $queryString, array $params): void
    {
        $stmt = $this->getEntityManager()->getConnection()->prepare($queryString);
        $stmt->execute($params);
        $stmt->closeCursor();
    }

    /**
     * Builds invalidation query based on given invalidation object. The invalidation is executed only
     * for active urls.
     */
    private function getInvalidationQueryString(InvalidationObjectInterface $invalidationObject): string
    {
        $query = 'UPDATE seo_url su ';

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

if (Kernel::VERSION_ID >= 42000) {
    abstract class AbstractSeoInvalidator extends AbstractSeoInvalidatorBase
        implements \Symfony\Contracts\Service\ServiceSubscriberInterface
    {

    }
} else {
    abstract class AbstractSeoInvalidator extends AbstractSeoInvalidatorBase
        implements \Symfony\Component\DependencyInjection\ServiceSubscriberInterface
    {

    }
}
