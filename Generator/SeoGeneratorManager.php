<?php
/**
 * This file is part of the "NFQ Bundles" package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nfq\SeoBundle\Generator;

/**
 * Class SeoGeneratorManager
 * @package Nfq\SeoBundle\Generator
 */
class SeoGeneratorManager
{
    /**
     * Holds route
     *
     * @var SeoGeneratorInterface[]
     */
    private $generators = [];

    /**
     * Maps route name to generator class. This is later use to detect if no more than one generator is used
     * per route name. Same generator can be used for multiple routes
     *
     * @var string[]
     */
    private $routeToGenerator = [];

    /**
     * @param SeoGeneratorInterface $generator
     * @param string $routeName
     * @throws \InvalidArgumentException
     * @return void
     */
    public function addGenerator(SeoGeneratorInterface $generator, $routeName)
    {
        $generatorClass = get_class($generator);

        if (!is_subclass_of($generator, 'Nfq\\SeoBundle\\Generator\\AbstractSeoGenerator')) {
            throw new \InvalidArgumentException(
                sprintf('Generator `%s` must extend `%s`',
                    $generatorClass,
                    'Nfq\\SeoBundle\\Generator\\AbstractSlugGenerator'
                ));
        }

        if ($this->isRouteRegistered($routeName)) {
            throw new \InvalidArgumentException(
                sprintf('Only one generator per route is supported. `%s` already has registered generator `%s`',
                    $routeName,
                    $this->routeToGenerator[$routeName]
                ));
        }

        $this->generators[$generatorClass] = $generator;
        $this->addToRouteMap($routeName, $generatorClass);
    }

    /**
     * @param string $routeName
     * @throws \InvalidArgumentException
     * @return SeoGeneratorInterface
     */
    public function getGenerator($routeName)
    {
        if (!$this->isRouteRegistered($routeName)) {
            throw new \InvalidArgumentException(
                sprintf('No generator for route `%s` found. Seo routes are: `%s`',
                $routeName,
                implode('`, `', array_keys($this->routeToGenerator))
                ));
        }

        $id = $this->routeToGenerator[$routeName];

        return $this->generators[$id]->setCurrentRouteName($routeName);
    }

    /**
     * @param string $routeName
     * @return bool
     */
    public function isRouteRegistered($routeName)
    {
        return array_key_exists($routeName, $this->routeToGenerator);
    }

    /**
     * @return array
     */
    public function getRegisteredRoutes()
    {
        return array_keys($this->routeToGenerator);
    }

    /**
     * Map route name to generator
     *
     * @param string $routeName
     * @param string $generator
     * @return void
     */
    private function addToRouteMap($routeName, $generator)
    {
        $this->routeToGenerator[$routeName] = $generator;
    }
}
