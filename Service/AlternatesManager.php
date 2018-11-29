<?php declare(strict_types=1);

/**
 * This file is part of the "NFQ Bundles" package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nfq\SeoBundle\Service;

use Nfq\SeoBundle\Entity\SeoInterface;
use Nfq\SeoBundle\Utils\SeoHelper;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class AlternatesManager
 * @package Nfq\SeoBundle\Service
 */
class AlternatesManager
{
    /** @var SeoManager */
    protected $sm;

    /** @var RouterInterface */
    protected $router;

    /** @var string[] */
    protected $locales;

    /** @var string[] */
    protected $alternateLocaleMapping;

    public function __construct(SeoManager $sm, RouterInterface $router, array $locales)
    {
        $this->sm = $sm;
        $this->router = $router;
        $this->locales = $locales;
        $this->alternateLocaleMapping = [];
    }

    /**
     * @return string[]
     */
    public function getAlternateLocaleMapping(): array
    {
        return $this->alternateLocaleMapping;
    }

    /**
     * Set mapped alternate urls, which are later switched. Example:
     *   en_GL => en_US
     * en_GL locale will be mapped to en_US, so instead of en_GL, en_US will be shown
     *
     *
     * @param string[] $alternateLocaleMapping
     */
    public function setAlternateLocaleMapping(array $alternateLocaleMapping): void
    {
        $this->alternateLocaleMapping = $alternateLocaleMapping;
    }

    public function getLangAlternates(SeoInterface $entity, array $routeParams): array
    {
        $result = [];

        $currentLocale = $routeParams['_locale'];
        $currentAlternates = $this->sm->getRepository()->getAlternatesArray(
            $entity->getRouteName(),
            $entity->getEntityId(),
            $currentLocale
        );

        //Add current locale because it should not be added to alternates
        $localesWithAlternates = [$currentLocale];
        foreach ($currentAlternates as $alternate) {
            $altLocale = $this->resolveAlternateUrlLocale($alternate['locale']);
            $result[$altLocale] = $this->buildAlternateUrl($alternate['seoUrl'], $alternate['locale']);
            $localesWithAlternates[] = $alternate['locale'];
        }

        return array_merge(
            $result,
            $this->generateLangAlternates($entity->getRouteName(), $routeParams, $localesWithAlternates)
        );
    }

    protected function buildAlternateUrl(string $seoUrl): string
    {
        $scheme = $this->router->getContext()->getScheme();
        $host = $this->router->getContext()->getHost();

        return $scheme . '://' . $host . $seoUrl;
    }

    protected function generateLangAlternates(string $routeName, array $params, array $localesWithAlternates): array
    {
        $alternates = [];
        $alternatesToGenerate = array_diff($this->locales, $localesWithAlternates);

        if (empty($alternatesToGenerate)) {
            return $alternates;
        }

        //Filter private (prefixed with _) variables
//        array_walk($params, function (&$value, $key) {
//            $value = strpos($key, '_') === 0 ? false : $value;
//        });
//        $params = array_filter($params);

        $backedUpStrategy = $this->router->getMissingUrlStrategy();
        $this->router->setMissingUrlStrategy('empty');

        foreach ($alternatesToGenerate as $locale) {
            $altLocale = $this->resolveAlternateUrlLocale($locale);

            $url = $this->router->generate(
                $routeName,
                array_merge($params, ['_locale' => $locale]),
                UrlGeneratorInterface::ABSOLUTE_URL
            );

            if ($url == '#' || empty($url)) {
                continue;
            }

            $alternates[$altLocale] = $url;
        }

        $this->router->setMissingUrlStrategy($backedUpStrategy);

        return $alternates;
    }

    private function resolveAlternateUrlLocale(string $locale): string
    {
        if (array_key_exists($locale, $this->alternateLocaleMapping)) {
            $locale = $this->alternateLocaleMapping[$locale];
        }

        return SeoHelper::formatAlternateLocale($locale);
    }
}
