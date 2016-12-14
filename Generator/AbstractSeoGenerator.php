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

use Doctrine\ORM\EntityManagerInterface;
use Nfq\SeoBundle\Model\SeoSlug;
use Nfq\SeoBundle\Model\SeoSlugInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\PropertyAccess\PropertyPathBuilder;

/**
 * Class AbstractSeoGenerator
 * @package Nfq\SeoBundle\Generator
 */
abstract class AbstractSeoGenerator implements SeoGeneratorInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var string
     */
    private $currentRouteName;

    /**
     * @param \Exception $exception
     * @param array $payload
     */
    protected function logException(\Exception $exception, array $payload)
    {
        if (!$this->logger) {
            throw new \RuntimeException('Set logger service for generator');
        }

        $this->logger->alert(
            $exception->getMessage() ? $exception->getMessage() : 'Failed to fill missing allowed parameters',
            $payload
        );
    }

    /**
     * @return SeoSlugInterface
     */
    protected function getSeoSlug()
    {
        $seoSlug = new SeoSlug();
        $seoSlug->setRouteName($this->getCurrentRouteName());

        return $seoSlug;
    }

    /**
     * {@inheritdoc}
     */
    public function setCurrentRouteName($routeName)
    {
        $this->currentRouteName = $routeName;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrentRouteName()
    {
        return $this->currentRouteName;
    }

    /**
     * @inheritdoc
     */
    public function setEntityManager(EntityManagerInterface $em)
    {
        $this->em = $em;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getEntityManager()
    {
        return $this->em;
    }

    /**
     * @param array $params
     * @param array $params2
     * @param array $params3
     * @return array
     */
    protected function buildAllowedQueryParams(array $params, array $params2 = [], array $params3 = [])
    {
        $allowedParams = $this->getAllowedQueryParams();
        $allowedParams = array_merge(['path' => true], $allowedParams);

        $arrays = array_replace_recursive($params, $params2, $params3);

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
    protected function setMissingAllowedParameters(array $allowedParams, array $allowedNotSetPaths, array $params)
    {
        return $allowedParams;
    }
}
