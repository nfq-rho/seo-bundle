<?php declare(strict_types=1);

/**
 * This file is part of the "NFQ Bundles" package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nfq\SeoBundle\EventListener;

use Nfq\SeoBundle\Entity\SeoInterface;
use Nfq\SeoBundle\Entity\SeoUrl;
use Nfq\SeoBundle\Service\SeoManager;
use Nfq\SeoBundle\Page\SeoPageInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class SeoRouterSubscriber
 * @package Nfq\SeoBundle\EventListener
 */
class SeoRouterSubscriber implements EventSubscriberInterface
{
    /** @var array */
    private $seoData;

    /** @var SeoManager */
    private $sm;

    /** @var SeoPageInterface */
    private $sp;

    /** @var RouterInterface */
    private $router;

    public function __construct(SeoManager $sm, SeoPageInterface $sp, RouterInterface $router)
    {
        $this->sm = $sm;
        $this->sp = $sp;
        $this->router = $router;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [
                ['onKernelRequest', 31],
            ]
        ];
    }

    public function onKernelRequest(GetResponseEvent $event): void
    {
        //We're only interested in master requests
        if (!$event->isMasterRequest() || $this->isDebugRequest($event->getRequest())) {
            return;
        }

        $this->hasSeoData($event->getRequest());

        $this->handleStdToSeo($event);

        if ($event->isPropagationStopped()) {
            return;
        }

        $this->handleSeoRedirect($event);

        if ($event->isPropagationStopped()) {
            return;
        }

        $this->setSeoPageData($event);
    }

    /**
     * @param Request $request
     * @return array|bool
     */
    private function hasSeoData(Request $request)
    {
        if ($this->seoData !== null) {
            return $this->seoData;
        }

        $this->seoData = false;

        if ($seoData = $request->attributes->get('__nfq_seo')) {
            $this->seoData = $seoData;
        }

        return $this->seoData;
    }

    private function handleSeoRedirect(GetResponseEvent $event): void
    {
        $request = $event->getRequest();

        if (!$seoData = $this->hasSeoData($request)) {
            return;
        }

        /** @var SeoInterface $seoEntity */
        $seoEntity = $seoData['entity'];

        if ($seoEntity->isOK() || $seoEntity->isInvalid()) {
            return;
        }

        $seoUrlRedirect = $this->sm->exchangeInactiveSeoUrlForActive($seoEntity);

        //If new active seo url was not found this means that page does not exist
        if (!$seoUrlRedirect) {
            /**
             * @TODO if url is inactive and active url is not yet generated, we should generate new url in matchStdUrl
             */
            throw new NotFoundHttpException();
        }

        $this->issueRedirect($event, $seoUrlRedirect);
    }

    private function handleStdToSeo(GetResponseEvent $event): void
    {
        $request = $event->getRequest();

        //Check if current request is a seo request, but with standard url
        //If so, redirect to correct seo url
        if (!$this->isSeoRequestAsStd($request->attributes)) {
            return;
        }

        $routeParameters = array_merge(
            $request->attributes->get('_route_params'),
            $request->query->all(),
            [
                'path' => $request->getPathInfo(),
            ]
        );

        $seoUrl = $this->sm->getActiveSeoUrl($request->attributes->get('_route'), $routeParameters);

        if (!$seoUrl) {
            $currentMissingUrlStrategy = $this->router->getMissingUrlStrategy();

            $this->router->setMissingUrlStrategy(null);
            $redirectToUrl = $this->router->generate($request->attributes->get('_route'), $routeParameters);

            $this->router->setMissingUrlStrategy($currentMissingUrlStrategy);

            if (!$redirectToUrl) {
                return;
            }
        } else {
            /**
             * @TODO: remove params from $requestQueryString which are in $seoUrl->getStdUrl()
             */
            $redirectToUrl = $this->getFullUri($seoUrl->getSeoUrl(), $event->getRequest()->getQueryString());
        }

        $this->issueRedirect($event, $redirectToUrl);
    }

    private function setSeoPageData(GetResponseEvent $event): void
    {
        $request = $event->getRequest();

        $this->sp->setLocale($request->getLocale());
        $this->sp->setHost($request->getSchemeAndHttpHost());
        $this->sp->setSimpleHost('http://' . $request->getHost());

        if (!$seoData = $this->hasSeoData($request)) {
            return;
        }

        /** @var SeoInterface $seoEntity */
        $seoEntity = $seoData['entity'];

        $fullUri = $this->getFullUri($seoData['url'], $request->getQueryString());

        //Check if current path is same as seo path.
        //This is for, for example, avoiding uppercase letters in SEO path
        /** @TODO add config value for `url_case_check` */
        if ($request->getPathInfo() !== $seoEntity->getSeoUrl()) {
            $event->setResponse(
                new RedirectResponse($fullUri, 301)
            );

            return;
        }

        $this->sp->setLinkCanonical($fullUri);
        $this->sp->setLangAlternates($seoData['alternates']);
    }

    private function issueRedirect(GetResponseEvent $event, string $url): void
    {
        $event->setResponse(
            new RedirectResponse($url, 301)
        );

        $event->stopPropagation();
    }

    private function getFullUri(string $path, ?string $queryString): string
    {
        return $path . (!$queryString ? '' : '?' . $queryString);
    }

    private function isDebugRequest(Request $request): bool
    {
        return \in_array($request->attributes->get('_route'), ['_wdt'], true);
    }

    private function isSeoRequestAsStd($requestAttributes): bool
    {
        return !$requestAttributes->has('__nfq_seo')
            && $this->sm->getGeneratorManager()->isRouteRegistered($requestAttributes->get('_route'));
    }
}
