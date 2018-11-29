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

    /** @var string[] */
    private $routeEntityMap = [];

    public function addInvalidator(SeoInvalidatorInterface $invalidator, string $routeName, string $entityClass): void
    {
        if (!isset($this->invalidators[$routeName])) {
            $this->invalidators[$routeName] = $invalidator;
        }

        $this->addToRouteEntityMap($routeName, $entityClass);
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function getInvalidator(string $entityClass): SeoInvalidatorInterface
    {
        foreach ($this->routeEntityMap as $routeName => $invalidatorClasses) {
            if (false !== $idx = array_search($entityClass, $invalidatorClasses)) {
                return $this->invalidators[$routeName]->setRouteName($routeName);
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
        if (isset($this->routeEntityMap[$type]) && in_array($entityClass, $this->routeEntityMap[$type])) {
            throw new \InvalidArgumentException("Duplicated entity `{$entityClass}` for type `{$type}` detected");
        }

        $this->routeEntityMap[$type][] = $entityClass;
    }
}
