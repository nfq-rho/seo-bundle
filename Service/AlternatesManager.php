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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class AlternatesManager
 * @package Nfq\SeoBundle\Service
 */
class AlternatesManager
{
    private const HREF_LANG_DEFAULT_KEY = 'x-default';

    /** @var SeoManager */
    protected $seoManager;

    /** @var RouterInterface */
    protected $router;

    /** @var string */
    protected $defaultLocale;

    /** @var string[] */
    protected $locales;

    /** @var string[] */
    protected $alternateLocaleMapping;

    public function __construct(SeoManager $seoManager, RouterInterface $router, string $defaultLocale, array $locales)
    {
        $this->seoManager = $seoManager;
        $this->router = $router;
        $this->defaultLocale = $defaultLocale;
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

    /**
     * @return array
     */
    public function getRegularUrlLangAlternates(Request $request): array
    {
        $locale = $request->getLocale();
        $routeName = $request->attributes->get('_route');

        $routeCollection = $this->router->getRouteCollection();

        // Try to find out real route name
        $route = $routeCollection->get($routeName) ?? $routeCollection->get($routeName . '.' . $locale) ?? $routeName;

        if ($route instanceof Route) {
            $route = $route->hasDefault('_canonical_route')
                ? $route->getDefault('_canonical_route') . '.' . $locale
                : $routeName;
        }

        $routeParams = array_replace($request->attributes->get('_route_params'), ['_locale' => $locale]);

        $result = $this->generateLangAlternates($route, $routeParams, []);

        if (isset($result[$this->defaultLocale])) {
            $result[self::HREF_LANG_DEFAULT_KEY] = $result[$this->defaultLocale];
        }

        return $result;
    }

    /**
     * Getting alternates is a two-step process:
     *   1 - get generated urls in all locales
     *   2 - generate missing urls in specific locale if any
     *
     * @param SeoInterface $entity
     * @param string[] $routeParams
     * @return string[]
     */
    public function getSeoUrlLangAlternates(SeoInterface $entity, array $routeParams): array
    {
        $result = [];

        $existingAlternates = $this->seoManager->getRepository()->getAlternatesArray(
            $entity->getRouteName(),
            $entity->getEntityId()
        );

        $localesWithAlternates = [];
        foreach ($existingAlternates as $alternate) {
            $altLocale = $this->resolveAlternateUrlLocale($alternate['locale']);
            $result[$altLocale] = $this->buildAlternateUrl($alternate['seoUrl']);
            $localesWithAlternates[] = $alternate['locale'];
        }

        $result = array_merge(
            $result,
            $this->generateLangAlternates($entity->getRouteName(), $routeParams, $localesWithAlternates)
        );

        if (isset($result[$this->defaultLocale])) {
            $result[self::HREF_LANG_DEFAULT_KEY] = $result[$this->defaultLocale];
        }

        return $result;
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

        $backedUpStrategy = $this->router->getMissingUrlStrategy();
        $this->router->setMissingUrlStrategy('empty');

        foreach ($alternatesToGenerate as $locale) {
            $altLocale = $this->resolveAlternateUrlLocale($locale);

            $url = $this->router->generate(
                $routeName,
                array_merge($params, ['_locale' => $locale]),
                UrlGeneratorInterface::ABSOLUTE_URL
            );

            if ($url === '#' || empty($url)) {
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
