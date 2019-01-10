<?php declare(strict_types=1);

/**
 * This file is part of the "NFQ Bundles" package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nfq\SeoBundle\Routing;

use Nfq\SeoBundle\Controller\ExceptionController;
use Nfq\SeoBundle\Entity\SeoInterface;
use Nfq\SeoBundle\Service\AlternatesManager;
use Nfq\SeoBundle\Service\SeoManager;
use Nfq\SeoBundle\Traits\SeoConfig;
use Nfq\SeoBundle\Utils\SeoHelper;
use Nfq\SeoBundle\Utils\SeoUtils;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class SeoRouterBase
 * @package Nfq\SeoBundle\Routing
 */
class SeoRouterBase implements RouterInterface, RequestMatcherInterface
{
    private const FORCE_SEO_MATCHER_FLAG = '_force_seo_matcher';
    public const SEO_EXCEPTION_CONTROLLER = ExceptionController::class . '::seoShowAction';

    use SeoConfig;

    /** @var ContainerInterface */
    private $locator;

    /** @var RouterInterface|RequestMatcherInterface */
    private $router;

    /** @var string */
    private $currentLocale;

    /** @var RequestContext */
    private $backedUpContext;

    /** @var string */
    private $defaultLocale;

    /** @var bool */
    private $debug;

    /**
     * Holds last generated Seo entity
     *
     * @var SeoInterface|bool
     */
    private $lastGeneratedSeoEntity = false;

    public function __construct(ContainerInterface $locator, RouterInterface $router, bool $debug)
    {
        $this->locator = $locator;
        $this->router = $router;

        $this->debug = $debug;
    }

    public static function getSubscribedServices(): array
    {
        return [
            SeoManager::class,
            AlternatesManager::class,
        ];
    }

    public function setDefaultLocale(string $defaultLocale): void
    {
        $this->defaultLocale = $defaultLocale;
    }

    public function match($pathInfo): array
    {
        try {
            $match = $this->router->match($pathInfo);

            if (isset($match[self::FORCE_SEO_MATCHER_FLAG])) {
                $match = $this->matchStdUrl($pathInfo);
            }
        } catch (ResourceNotFoundException $ex) {
            $match = $this->matchStdUrl($pathInfo);
        }

        return $match;
    }

    public function matchRequest(Request $request): array
    {
        try {
            $match = $this->router->matchRequest($request);

            if (isset($match[self::FORCE_SEO_MATCHER_FLAG])) {
                $match = $this->matchStdUrl($request);
            }
        } catch (ResourceNotFoundException $ex) {
            $match = $this->matchStdUrl($request);
        }

        return $match;
    }

    public function isSeoRoute(string $routeName): bool
    {
        return $this->locator->get(SeoManager::class)->getGeneratorManager()->isRouteRegistered($routeName);
    }

    public function getRouteCollection(): RouteCollection
    {
        return $this->router->getRouteCollection();
    }

    public function setContext(RequestContext $context): void
    {
        $this->router->setContext($context);
    }

    public function getContext(): RequestContext
    {
        return $this->router->getContext();
    }

    public function generate($name, $parameters = [], $absolute = self::ABSOLUTE_PATH): string
    {
        $stdUrlParsed = [];

        try {
            //Generates standard url
            $stdUrl = $this->router->generate($name, $parameters, $absolute);

            //Check if given route name is not registered as seo route
            if (!$this->isSeoRoute($name)) {
                return $stdUrl;
            }

            //Usually when generating url from twig, we do not specify locale, that is why we must
            //explicitly set it
            $this->setLocale($parameters);

            //Building explicit $routeParameters array by adding some of control parameters
            $stdUrlParsed = parse_url($stdUrl);
            $routeParameters = array_merge(
                $parameters,
                [
                    'path' => $stdUrlParsed['path'],
                ]
            );

            $seoEntity = $this->locator->get(SeoManager::class)->getActiveSeoUrl($name, $routeParameters);

            //If active SEO url was not found, generate a new one and use it instead
            if (!$seoEntity && false === ($seoEntity = $this->locator->get(SeoManager::class)->createSeoUrl(
                    $name,
                    $routeParameters
                ))
            ) {
                throw new RouteNotFoundException();
            }

            $this->lastGeneratedSeoEntity = $seoEntity;
            $seoUrl = $seoEntity->getSeoUrl();

            //Re-add query parameters from given parameter array back to new SEO url
            if (isset($stdUrlParsed['query'])) {
                $stdQueryParsed = SeoHelper::parseQueryString($stdUrlParsed['query']);

                $seoStdParsed = $seoEntity->getParsedStdUrl(true);

                if (isset($seoStdParsed['query'])) {
                    $seoStdQueryParsed = SeoHelper::parseQueryString($seoStdParsed['query']);
                    //remove query parameters that were used to generate SEO uri and
                    //re-attach them to new std uri
                    $newStdUrlParams = SeoUtils::recursiveUnsetExisting($stdQueryParsed, $seoStdQueryParsed);
                } else {
                    $newStdUrlParams = $stdQueryParsed;
                }

                $seoUrl = SeoHelper::getUri($seoUrl, $newStdUrlParams);
            }

            if ($absolute === self::ABSOLUTE_URL || $absolute === self::NETWORK_PATH) {
                $seoUrl = sprintf(
                    '%s://%s%s%s',
                    $stdUrlParsed['scheme'] ?? $this->getContext()->getScheme(),
                    $stdUrlParsed['host'] ?? $this->getContext()->getHost(),
                    isset($stdUrlParsed['port']) && $stdUrlParsed['port'] !== 80 ? ':' . $stdUrlParsed['port'] : '',
                    $seoUrl
                );
            }
        } catch (RouteNotFoundException $ex) {
            //If in debug mode rethrow exception
            if ($this->debug && !$this->isSeoRoute($name)) {
                throw $ex;
            }

            if (null === $seoUrl = $this->applyMissingUrlStrategy($parameters, $stdUrlParsed)) {
                $seoUrl = $stdUrl;
            }
        }

        return $seoUrl;
    }

    /**
     * @param string[] $routeParams
     * @param string[] $stdUrlParsed
     */
    private function applyMissingUrlStrategy(array $routeParams, array $stdUrlParsed): ?string
    {
        $result = null;

        switch ($this->getMissingUrlStrategy()) {
            case 'empty_host':
                $result = sprintf(
                    '%s://%s%s/',
                    $this->getContext()->getScheme(),
                    $this->getContext()->getHost(),
                    80 !== $this->getContext()->getHttpPort() ? ':' . $this->getContext()->getHttpPort() : ''
                );
                break;
            case 'empty_host_with_locale':
                $result = sprintf(
                    '%s://%s%s/%s/',
                    $this->getContext()->getScheme(),
                    $this->getContext()->getHost(),
                    80 !== $this->getContext()->getHttpPort() ? ':' . $this->getContext()->getHttpPort() : '',
                    $routeParams['_locale']
                );
                break;
            case 'empty':
                $result = '';
                break;
            case 'ignore':
                $result = '#';
                break;
            default:
                break;
        }

        return $result;
    }

    /**
     * @return SeoInterface|bool
     */
    private function getLastGeneratedSeoEntity()
    {
        return $this->lastGeneratedSeoEntity;
    }

    /**
     * Sets locale to given parameters array if it is not set. The locale is taken from current request context.
     *
     * @param string[] $parameters
     */
    private function setLocale(array &$parameters): void
    {
        if (isset($parameters['_locale'])) {
            return;
        }

        $this->currentLocale = $this->getContext()->getParameter('_locale');
        $parameters['_locale'] = $this->currentLocale ?: $this->defaultLocale;
    }

    /**
     * Route was not found, try to find and match a SEO url
     *
     * @param Request|string $matchData
     * @return array
     */
    private function matchStdUrl($matchData): array
    {
        $result = [
            '_route' => '',
            '__nfq_seo' => [],
            '_controller' => self::SEO_EXCEPTION_CONTROLLER,
            'exception' => new NotFoundHttpException($this->getNotFoundMessage(), null, 404)
        ];

        if ($matchData instanceof Request) {
            $pathInfo = $matchData->getPathInfo();
            $locale = $matchData->getLocale();
        } else {
            $pathInfo = $matchData;
            $locale = $this->router->getContext()->getParameter('_locale');
        }

        if (null === ($stdUrl = $this->locator->get(SeoManager::class)->getStdUrl($pathInfo, $locale))) {
            return $result;
        }

        $stdParsed = $stdUrl->getParsedStdUrl();
        $stdQueryParameters = isset($stdParsed['query']) ? SeoHelper::parseQueryString($stdParsed['query']) : [];

        //Get request parameters before creating seo context
        $requestParams = SeoHelper::parseQueryString($this->router->getContext()->getQueryString());

        $this->createSeoContext($stdParsed);

        //Try to match standard url to get real controller
        $stdMatch = $this->router->match($stdParsed['path']);

        $stdMatchParameters = $stdMatch;
        unset($stdMatchParameters['_controller'], $stdMatchParameters['_route']);

        $routeParams = array_replace_recursive($requestParams, $stdMatchParameters, $stdQueryParameters);

        //Based on url status, decide what to do next
        switch ($stdUrl->getStatus()) {
            //For invalid urls, we just merge everything and return
            case SeoInterface::STATUS_INVALID:
                $result += $stdMatch;
                break;
            case SeoInterface::STATUS_REDIRECT:
                //Try to generate new seo url
                $this->generate($stdMatch['_route'], $routeParams);
                $stdUrl = $this->getLastGeneratedSeoEntity();

                //Failed to generated new SEO url
                if (empty($stdUrl)) {
                    break;
                }
            // no break
            case SeoInterface::STATUS_OK:
                //If stdMatch is seo route, add specific flag to it's parameters, otherwise do not continue
                if (!$this->isSeoRoute($stdMatch['_route'])) {
                    break;
                }

                //Get query parameters from standard uri which should be added back to current uri
                $this->setRequestParameters(
                    $matchData,
                    SeoUtils::diffKeyRecursive($stdQueryParameters, $requestParams)
                );

                ($matchData instanceof Request) && $matchData->attributes->set('__nfq_seo', [
                    'entity' => $stdUrl,
                    'url' => sprintf(
                        '%s://%s%s%s',
                        $this->getContext()->getScheme(),
                        $this->getContext()->getHost(),
                        80 !== $this->getContext()->getHttpPort()
                            ? ':' . $this->getContext()->getHttpPort()
                            : '',
                        $stdUrl->getSeoUrl()
                    ),
                    'alternates' => $this->locator->get(AlternatesManager::class)->getLangAlternates(
                        $stdUrl,
                        $routeParams
                    ),
                ]);

                $result = $stdMatch;
                break;
        }

        $this->restoreContext();

        return $result;
    }

    /**
     * Backs up current context, since SEO check does new URI matching it does generate new context.
     */
    private function backupContext(): void
    {
        $this->backedUpContext = clone $this->getContext();
    }

    /**
     * Restores context from backup.
     */
    private function restoreContext(): void
    {
        $this->setContext($this->backedUpContext);
        $this->backedUpContext = null;
    }

    /**
     * Creates SEO context, part of the data is based on current context.
     *
     * @param string[] $parsedStdUrl
     */
    private function createSeoContext(array $parsedStdUrl): void
    {
        $this->backupContext();

        $seoContext = new RequestContext(
            '',
            'GET',
            $this->getContext()->getHost(),
            $this->getContext()->getScheme(),
            $this->getContext()->getHttpPort(),
            $this->getContext()->getHttpsPort(),
            $parsedStdUrl['path'],
            $parsedStdUrl['query'] ?? ''
        );

        $seoContext->setParameters($this->getContext()->getParameters());

        $this->setContext($seoContext);
    }

    /**
     * @param Request|string $matchData
     * @param string[] $parameters
     */
    private function setRequestParameters($matchData, array $parameters): void
    {
        if (empty($parameters)) {
            return;
        }

        if ($matchData instanceof Request) {
            $current = $matchData->query->all();
            $new = array_replace_recursive($current, $parameters);
            $matchData->query->replace($new);
        } else {
            $queryToAppend = http_build_query($parameters, '', '&');

            $contextQueryString = $this->backedUpContext->getQueryString();
            $this->backedUpContext->setQueryString(
                empty($contextQueryString)
                    ? $queryToAppend
                    : '&' . $queryToAppend
            );
        }
    }
}

if (Kernel::VERSION_ID >= 40200) {
    class SeoRouter extends SeoRouterBase implements \Symfony\Contracts\Service\ServiceSubscriberInterface
    {

    }
} else {
    class SeoRouter extends SeoRouterBase implements \Symfony\Component\DependencyInjection\ServiceSubscriberInterface
    {

    }
}
