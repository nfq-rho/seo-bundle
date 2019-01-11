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
        }

        $this->addToRouteEntityMap($routeName, $entityClass);
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function getInvalidator(string $entityClass, string $eventName): SeoInvalidatorInterface
    {
        foreach ($this->routeEntityMap as $routeName => $invalidatorClasses) {
            if (\in_array($entityClass, $invalidatorClasses, true)) {
                return $this->invalidators[$eventName][$routeName]->setRouteName($routeName);
            }
        }

        throw new \InvalidArgumentException("No invalidator for entity `{$entityClass}` found");
    }

    /**
     * Map paths to type
     *
     * @throws \InvalidArgumentException
     */
    private function addToRouteEntityMap(string $type, string $entityClass): void
    {
        if (isset($this->routeEntityMap[$type]) && \in_array($entityClass, $this->routeEntityMap[$type], true)) {
            throw new \InvalidArgumentException("Duplicated entity `{$entityClass}` for type `{$type}` detected");
        }

        $this->routeEntityMap[$type][] = $entityClass;
    }
}
