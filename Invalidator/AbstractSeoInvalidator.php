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
        $queryString = 'UPDATE seo_url su SET su.status = :invalid_status WHERE su.route_name = :route_name AND su.entity_id = :entity_id';

        $whereParams = array_merge([
            'route_name' => $this->getRouteName(),
            'entity_id' => $invalidationObject->getEntity()->getId(),
        ], $invalidationObject->getWhereParams(), [
            'invalid_status' => SeoInterface::STATUS_INVALID,
        ]);

        $whereParams = array_filter($whereParams, function ($value) {
            return $value !== null && $value !== [];
        });

        $this->executeStatement($queryString, $whereParams);
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function executeInvalidation(InvalidationObjectInterface $invalidationObject): void
    {
        if (!$invalidationObject->hasChanges()) {
            return;
        }

        $whereParams = array_merge([
            'route_name' => $this->getRouteName(),
            'entity_id' => $invalidationObject->getEntity()->getId(),
            'locale' => $invalidationObject->getLocale(),
        ], $invalidationObject->getWhereParams(), [
            'active_status' => SeoInterface::STATUS_OK,
            'target_status' => $invalidationObject->getInvalidationStatus()
        ]);

        $whereParams = array_filter($whereParams, function ($value) {
            return $value !== null && $value !== [];
        });

        $this->executeStatement(
            $this->getInvalidationQueryString($invalidationObject, $whereParams),
            $whereParams
        );
    }

    /**
     * @param string[] $params
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function executeStatement(string $queryString, array $params): void
    {
        // Convert all params to string
        $params = array_map(function ($param): string {
            return \is_array($param) ? implode(',', $param) : (string)$param;
        }, $params);

        $stmt = $this->getEntityManager()->getConnection()->prepare($queryString);
        $stmt->execute($params);
        $stmt->closeCursor();
    }

    /**
     * Builds invalidation query based on given invalidation object. The invalidation is executed only
     * for active urls.
     */
    private function getInvalidationQueryString(
        InvalidationObjectInterface $invalidationObject,
        array $whereParams
    ): string {
        $query = 'UPDATE seo_url su';

        if ($joinPart = $invalidationObject->getJoinPart()) {
            $query .= sprintf(' JOIN %s ', $joinPart);
        }

        $query .= ' SET su.status = :target_status WHERE su.status = :active_status ';

        if (isset($whereParams['locale'])) {
            $query .= ' AND su.locale = :locale ';
        }

        $query .= ' AND ( ';

        if (isset($whereParams['route_name'], $whereParams['entity_id'])) {
            $query .= ' (su.route_name = :route_name AND su.entity_id = :entity_id) ';
        }

        if ($wherePart = $invalidationObject->getWherePart()) {
            $query .= ' ' . $wherePart  . ' ';
        }

        $query .= ' ) ';

        return $query;
    }
}

if (Kernel::VERSION_ID >= 40200) {
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
