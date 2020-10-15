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

use Doctrine\DBAL\Connection;
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
    /** @var string[] */
    private $routes;

    /** @var ContainerInterface */
    private $locator;

    public function __construct(ContainerInterface $locator)
    {
        $this->locator = $locator;
        $this->routes = [];
    }

    public static function getSubscribedServices(): array
    {
        return [
            'doctrine' => EntityManagerInterface::class,
        ];
    }

    public function addRoute(string $routeName): void
    {
        if (in_array($routeName, $this->routes, true)) {
            return;
        }

        $this->routes[] = $routeName;
    }

    public function getRoutes(): array
    {
        return $this->routes;
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
        $whereParams = array_merge(
            [
                'route_names' => $this->getRoutes(),
            ],
            $invalidationObject->getWhereParams(),
            [
                'target_status' => SeoInterface::STATUS_INVALID,
            ]
        );

        $whereParams = array_filter($whereParams, function ($value) {
            return $value !== null && $value !== [];
        });

        $this->executeStatement(
            $this->getRemovalQueryString($invalidationObject, $whereParams),
            $whereParams,
            $invalidationObject->getWhereParamTypes()
        );
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function executeInvalidation(InvalidationObjectInterface $invalidationObject): void
    {
        if (!$invalidationObject->hasChanges()) {
            return;
        }

        $whereParams = array_merge(
            [
                'locale' => $invalidationObject->getLocale(),
                'route_names' => $this->getRoutes(),
            ],
            $invalidationObject->getWhereParams(),
            [
                'active_status' => SeoInterface::STATUS_OK,
                'target_status' => $invalidationObject->getInvalidationStatus()
            ]
        );

        $whereParams = array_filter($whereParams, function ($value) {
            return $value !== null && $value !== [];
        });

        $this->executeStatement(
            $this->getInvalidationQueryString($invalidationObject, $whereParams),
            $whereParams,
            $invalidationObject->getWhereParamTypes()
        );
    }

    /**
     * @param string[] $params
     * @param string[] $types
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function executeStatement(string $queryString, array $params, array $types): int
    {
        $conn = $this->getEntityManager()->getConnection();
        return $conn->executeUpdate($queryString, $params,
            array_merge(
                [
                    'route_names' => Connection::PARAM_STR_ARRAY,
                ],
                $types
            )
        );
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

        if (isset($whereParams['route_names'], $whereParams['entity_id'])) {
            $query .= ' (su.route_name IN (:route_names) AND su.entity_id = :entity_id) ';
        }

        if ($wherePart = $invalidationObject->getWherePart()) {
            $query .= ' ' . $wherePart . ' ';
        }

        $query .= ' ) ';

        return $query;
    }

    /**
     * Builds removal query based on given invalidation object. The invalidation is executed only
     * for active urls.
     */
    private function getRemovalQueryString(
        InvalidationObjectInterface $invalidationObject,
        array $whereParams
    ): string {
        $query = 'UPDATE seo_url su';

        if ($joinPart = $invalidationObject->getJoinPart()) {
            $query .= sprintf(' JOIN %s ', $joinPart);
        }

        $query .= ' SET su.status = :target_status WHERE';

        $query .= ' ( ';

        if (isset($whereParams['route_names'], $whereParams['entity_id'])) {
            $query .= ' (su.route_name IN (:route_names) AND su.entity_id = :entity_id) ';
        }

        if ($wherePart = $invalidationObject->getWherePart()) {
            $query .= ' ' . $wherePart . ' ';
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
