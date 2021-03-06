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

use Nfq\SeoBundle\Traits\SeoConfig;

/**
 * Class SeoInvalidatorManager
 * @package Nfq\SeoBundle\Invalidator
 */
class SeoInvalidatorManager
{
    use SeoConfig;

    /**
     * @var SeoInvalidatorInterface[]
     */
    private $invalidators = [];

    /**
     * @var array
     */
    private $routeEntityMap = [];

    /**
     * @param SeoInvalidatorInterface $invalidator
     * @param string $routeName
     * @param string $entityClass
     */
    public function addInvalidator(SeoInvalidatorInterface $invalidator, $routeName, $entityClass)
    {
        if (!isset($this->invalidators[$routeName])) {
            $this->invalidators[$routeName] = $invalidator;
        }

        $this->addToRouteEntityMap($routeName, $entityClass);
    }

    /**
     * @param string $entityClass
     * @return SeoInvalidatorInterface
     *
     * @throws \InvalidArgumentException
     */
    public function getInvalidator($entityClass)
    {
        foreach($this->routeEntityMap as $routeName => $invalidatorClasses) {
            if (false !== $idx = array_search($entityClass, $invalidatorClasses)) {
                return $this->invalidators[$routeName]->setRouteName($routeName);
            }
        }

        throw new \InvalidArgumentException("No invalidator for entity `{$entityClass}` found");
    }

    /**
     * Map paths to type
     *
     * @param string $type
     * @param string $entityClass
     *
     * @throws \InvalidArgumentException
     */
    private function addToRouteEntityMap($type, $entityClass)
    {
        if (isset($this->routeEntityMap[$type]) && in_array($entityClass, $this->routeEntityMap[$type])) {
            throw new \InvalidArgumentException("Duplicated entity `{$entityClass}` for type `{$type}` detected");
        }

        $this->routeEntityMap[$type][] = $entityClass;
    }
}
