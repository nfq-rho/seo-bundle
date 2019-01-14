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

/**
 * Class SeoInvalidatorManager
 * @package Nfq\SeoBundle\Invalidator
 */
class SeoInvalidatorManager
{
    /** @var SeoInvalidatorInterface[] */
    private $invalidators = [];

    /** @var string[][] */
    private $routeEntityMap = [];

    public function addInvalidator(
        SeoInvalidatorInterface $invalidator,
        string $routeName,
        string $entityClass,
        string $onEvents
    ): void {
        $onEventsArr = explode(',', $onEvents);

        foreach ($onEventsArr as $onEvent) {
            if (!isset($this->invalidators[$onEvent])) {
                $this->invalidators[$onEvent] = [];
            }

            $this->invalidators[$onEvent][$routeName] = $invalidator;
            $invalidator->addRoute($routeName);
        }

        $this->addToRouteEntityMap($routeName, $entityClass);
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function getInvalidator(string $entityClass, string $eventName): SeoInvalidatorInterface
    {
        $results = [];

        foreach ($this->routeEntityMap as $routeName => $invalidatorClasses) {
            if (\in_array($entityClass, $invalidatorClasses, true)) {
                $results[] = $this->invalidators[$eventName][$routeName];
            }
        }

        if (empty($results)) {
            throw new \InvalidArgumentException("No invalidator for entity `{$entityClass}` found");
        }

        return $results[1] ?? $results[0];
    }

    /**
     * @throws \InvalidArgumentException
     */
    private function addToRouteEntityMap(string $routeName, string $entityClass): void
    {
        if (isset($this->routeEntityMap[$routeName])
            && \in_array($entityClass, $this->routeEntityMap[$routeName], true)) {
            throw new \InvalidArgumentException("Duplicated entity `{$entityClass}` for route `{$routeName}` detected");
        }

        $this->routeEntityMap[$routeName][] = $entityClass;
    }
}
