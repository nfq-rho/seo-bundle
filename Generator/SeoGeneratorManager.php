<?php declare(strict_types=1);

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
    /** @var SeoGeneratorInterface[] */
    private $generators = [];

    /**
     * Maps route name to generator class. This is later use to detect if no more than one generator is used
     * per route name. Same generator can be used for multiple routes
     *
     * @var string[]
     */
    private $routeToGenerator = [];

    /**
     * @throws \InvalidArgumentException
     */
    public function addGenerator(SeoGeneratorInterface $generator, string $routeName): void
    {
        $generatorClass = \get_class($generator);

        if (!is_subclass_of($generator, AbstractSeoGenerator::class)) {
            throw new \InvalidArgumentException(
                sprintf('Generator `%s` must extend `%s`',
                    $generatorClass,
                    AbstractSeoGenerator::class
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
     * @throws \InvalidArgumentException
     */
    public function getGenerator(string $routeName): SeoGeneratorInterface
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

    public function isRouteRegistered(string $routeName): bool
    {
        return array_key_exists($routeName, $this->routeToGenerator);
    }

    /**
     * @return string[]
     */
    public function getRegisteredRoutes(): array
    {
        return array_keys($this->routeToGenerator);
    }

    private function addToRouteMap(string $routeName, string $generator): void
    {
        $this->routeToGenerator[$routeName] = $generator;
    }
}
