<?php
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
use Nfq\SeoBundle\Service\SeoManager;
use Nfq\SeoBundle\Page\SeoPageInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * Class SeoRouterSubscriber
 * @package Nfq\SeoBundle\EventListener
 */
class SeoRouterSubscriber implements EventSubscriberInterface
{
    /**
     * @var array
     */
    private $seoData;

    /**
     * @var SeoManager
     */
    private $sm;

    /**
     * @var SeoPageInterface
     */
    private $sp;

    /**
     * @param SeoManager $sm
     * @param SeoPageInterface $sp
     */
    public function __construct($sm, SeoPageInterface $sp)
    {
        $this->sm = $sm;
        $this->sp = $sp;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [
                ['onKernelRequest', 31],
            ]
        ];
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
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

    /**
     * @param GetResponseEvent $event
     */
    private function handleSeoRedirect(GetResponseEvent $event)
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

    /**
     * @param GetResponseEvent $event
     */
    private function handleStdToSeo(GetResponseEvent $event)
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

        //Send permanent redirect from standard to seo URL
        if (!$seoUrl) {
            return;
        }

        $this->issueRedirect($event, $seoUrl);
    }

    /**
     * @param GetResponseEvent $event
     */
    private function setSeoPageData(GetResponseEvent $event)
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

    /**
     * @param string $path
     * @param string $queryString
     * @return string
     */
    private function getFullUri($path, $queryString)
    {
        return $path . (!$queryString ? '' : '?' . $queryString);
    }

    /**
     * @param Request $request
     * @return bool
     */
    private function isDebugRequest(Request $request)
    {
        return in_array($request->attributes->get('_route'), ['_wdt']);
    }

    /**
     * @param ParameterBag $requestAttributes
     * @return bool
     */
    private function isSeoRequestAsStd($requestAttributes)
    {
        return !$requestAttributes->has('__nfq_seo')
            && $this->sm->getGeneratorManager()->isRouteRegistered($requestAttributes->get('_route'));
    }

    /**
     * @param GetResponseEvent $event
     * @param SeoInterface $seoUrl
     */
    private function issueRedirect(GetResponseEvent $event, SeoInterface $seoUrl)
    {
        /**
         * @TODO: remove params from $requestQueryString which are in $seoUrl->getStdUrl()
         */
        $event->setResponse(
            new RedirectResponse(
                $this->getFullUri($seoUrl->getSeoUrl(), $event->getRequest()->getQueryString()), 301
            )
        );

        $event->stopPropagation();
    }
}
