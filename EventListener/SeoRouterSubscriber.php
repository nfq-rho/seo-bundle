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
use Nfq\SeoBundle\Routing\SeoRouter;
use Nfq\SeoBundle\Service\SeoManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class SeoRouterSubscriber
 * @package Nfq\SeoBundle\EventListener
 */
class SeoRouterSubscriber implements EventSubscriberInterface
{
    /** @var SeoManager */
    private $sm;

    /** @var SeoRouter|RouterInterface */
    private $router;

    public function __construct(SeoManager $sm, RouterInterface $router)
    {
        $this->sm = $sm;
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

        $this->handleStdToSeoRedirect($event);

        if ($event->isPropagationStopped()) {
            return;
        }

        $seoData = $this->extractSeoDataFromRequest($event->getRequest());

        $this->handleCaseRedirect($event, $seoData);

        if ($event->isPropagationStopped()) {
            return;
        }

        $this->handleSeoRedirect($event, $seoData);

        if ($event->isPropagationStopped()) {
            return;
        }
    }

    private function extractSeoDataFromRequest(Request $request): ?array
    {
        return $request->attributes->get('__nfq_seo');
    }

    private function handleCaseRedirect(GetResponseEvent $event, ?array $seoData): void
    {
        if (null === $seoData) {
            return;
        }

        $request = $event->getRequest();

        /** @var SeoInterface $seoEntity */
        $seoEntity = $seoData['entity'];

        //Check if current path is same as seo path.
        //This is for, for example, avoiding uppercase letters in SEO path
        /** @TODO add config value for `url_case_check` */
        if ($request->getPathInfo() !== $seoEntity->getSeoUrl()) {
            $redirectToUrl = $this->getFullUri($seoEntity->getSeoUrl(), $request->getQueryString());

            $this->issueRedirect($event, $redirectToUrl);
        }
    }

    private function handleSeoRedirect(GetResponseEvent $event, ?array $seoData): void
    {
        if (null === $seoData) {
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

        $redirectToUrl = $this->getFullUri($seoUrlRedirect->getSeoUrl(), $event->getRequest()->getQueryString());

        $this->issueRedirect($event, $redirectToUrl);
    }

    private function handleStdToSeoRedirect(GetResponseEvent $event): void
    {
        $request = $event->getRequest();

        //Check if current request is a seo request, but with standard url
        //If so, redirect to correct seo url
        if (!$this->isSeoRequestAsStd($request->attributes)) {
            return;
        }

        $routeName = $request->attributes->get('_route');
        $routeParameters = array_merge(
            $request->attributes->get('_route_params'),
            $request->query->all()
        );

        $redirectToUrl = $this->router->generate($routeName, $routeParameters);

        //Seo url can't be generated just continue the request
        if ($redirectToUrl === $request->getPathInfo()) {
            return;
        }

        $this->issueRedirect($event, $redirectToUrl);
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
        return $request->attributes->get('_route') === '_wdt';
    }

    private function isSeoRequestAsStd(ParameterBag $requestAttributes): bool
    {
        return !$requestAttributes->has('__nfq_seo')
            && $this->sm->getGeneratorManager()->isRouteRegistered($requestAttributes->get('_route'));
    }
}
