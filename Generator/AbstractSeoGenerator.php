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

use Doctrine\ORM\EntityManagerInterface;
use Nfq\SeoBundle\Model\SeoSlug;
use Nfq\SeoBundle\Model\SeoSlugInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\PropertyAccess\PropertyPathBuilder;

/**
 * Class AbstractSeoGenerator
 * @package Nfq\SeoBundle\Generator
 */
abstract class AbstractSeoGeneratorBase implements SeoGeneratorInterface
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
            EntityManagerInterface::class,
        ];
    }

    protected function getSeoSlug(): SeoSlugInterface
    {
        $seoSlug = new SeoSlug();
        $seoSlug->setRouteName($this->getCurrentRouteName());

        return $seoSlug;
    }

    public function setCurrentRouteName(string $routeName): SeoGeneratorInterface
    {
        $this->currentRouteName = $routeName;
        return $this;
    }

    public function getCurrentRouteName(): string
    {
        return $this->currentRouteName;
    }

    public function getEntityManager(): EntityManagerInterface
    {
        return $this->locator->get(EntityManagerInterface::class);
    }

    protected function buildAllowedQueryParams(array ...$queryParams)
    {
        $allowedParams = $this->getAllowedQueryParams();
        $allowedParams = array_merge(['path' => true], $allowedParams);

        $params = array_shift($queryParams);

        $arrays = array_replace_recursive($params, ...$queryParams);

        $recIteratorIt = new \RecursiveIteratorIterator(
            new \RecursiveArrayIterator($allowedParams),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        $ppBuilder = new PropertyPathBuilder();
        $pa = new PropertyAccessor();
        $depthBefore = -1;

        $allowedNotSetPaths = [];
        foreach ($recIteratorIt as $key => $value) {
            $depthCurrent = $recIteratorIt->getDepth();

            if ($depthCurrent < $depthBefore) {
                $ppBuilder->remove($depthCurrent, $ppBuilder->getLength() - $depthCurrent - 1);
                $ppBuilder->replaceByIndex($depthCurrent, $key);
            } elseif ($depthCurrent > $depthBefore) {
                $ppBuilder->appendIndex($key);
            } else {
                $ppBuilder->replaceByIndex($depthCurrent, $key);
            }

            $depthBefore = $depthCurrent;

            //If there is a value that we should replace
            $propPath = $ppBuilder->getPropertyPath();

            //We are not interested in parent elements
            if ($recIteratorIt->callHasChildren()
                //Skip properties which are not allowed
                || null === $pa->getValue($allowedParams, $propPath)
            ) {
                continue;
            }

            //Add not set allowed properties, which must be set later
            if (null === $pa->getValue($arrays, $propPath)) {
                $allowedNotSetPaths[$key] = $propPath;
                continue;
            }

            $pa->setValue($allowedParams, $propPath, $pa->getValue($arrays, $propPath));
        }

        return empty($allowedNotSetPaths)
            ? $allowedParams
            : $this->setMissingAllowedParameters($allowedParams, $allowedNotSetPaths, $arrays);
    }

    /**
     * Set missing allowed parameters. Allowed parameters are considered required when generating SEO URI.
     *
     * @param array $allowedParams contains allowed parameters, which will be used later when generating stdHash
     * @param array $allowedNotSetPaths contains paths for PropertyAccessor in $allowedParams array which were not set
     * @param array $params contains all available parameters for that request
     * @return mixed
     */
    protected function setMissingAllowedParameters(
        array $allowedParams,
        array $allowedNotSetPaths,
        array $params
    ): array {
        return $allowedParams;
    }
}

if (Kernel::VERSION_ID >= 42000) {
    abstract class AbstractSeoGenerator extends AbstractSeoGeneratorBase
        implements \Symfony\Contracts\Service\ServiceSubscriberInterface
    {

    }
} else {
    abstract class AbstractSeoGenerator extends AbstractSeoGeneratorBase
        implements \Symfony\Component\DependencyInjection\ServiceSubscriberInterface
    {

    }
}
