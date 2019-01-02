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

use Nfq\SeoBundle\Controller\SeoAwareControllerInterface;
use Nfq\SeoBundle\Page\SeoPageInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class SeoPageDataSubscriber
 * @package Nfq\SeoBundle\EventListener
 */
class SeoPageDataSubscriber implements EventSubscriberInterface
{
    private const SEO_CONTROLLER_REQUEST_ATTRIBUTE = '__nfq_seo.controller';

    /** @var SeoPageInterface */
    private $sp;

    public function __construct(SeoPageInterface $sp)
    {
        $this->sp = $sp;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => ['onKernelController'],
            KernelEvents::REQUEST => ['onKernelRequest'],
        ];
    }

    public function onKernelController(FilterControllerEvent $event): void
    {
        $controller = $event->getController();

        if (!\is_array($controller)) {
            return;
        }

        if ($controller[0] instanceof SeoAwareControllerInterface) {
            $event->getRequest()->attributes->set(self::SEO_CONTROLLER_REQUEST_ATTRIBUTE, true);
        }
    }

    public function onKernelRequest(GetResponseEvent $event): void
    {
        $request = $event->getRequest();

        //We're only interested in master seo requests
        if (!$event->isMasterRequest() || $this->isDebugRequest($request)) {
            return;
        }

        $this->setGenericSeoPageData($request);
    }

    private function setGenericSeoPageData(Request $request): void
    {
        $this->sp->setLocale($request->getLocale());
        $this->sp->setHost($request->getSchemeAndHttpHost());
        $this->sp->setSimpleHost('http://' . $request->getHost());

        $this->sp->addMeta('property', 'og:url', $this->sp->formatCanonicalUri($request->getUri()));
        $this->sp->addMeta('property', 'og:type', 'website');

        if (!$this->isSeoRequest($request)) {
            return;
        }

        $seoData = $this->extractSeoDataFromRequest($request);

        if (null === $seoData) {
            return;
        }

        $fullUri = $this->getFullUri($seoData['url'], $request->getQueryString());

        $this->sp->setLinkCanonical($fullUri);

        //Overwrite og:url with canonical url
        $this->sp->addMeta('property', 'og:url', $fullUri);
        $this->sp->setLangAlternates($seoData['alternates']);
    }

    private function getFullUri(string $path, ?string $queryString): string
    {
        return $this->sp->formatCanonicalUri($path . (!$queryString ? '' : '?' . $queryString));
    }

    private function isDebugRequest(Request $request): bool
    {
        return $request->attributes->get('_route') === '_wdt';
    }

    private function isSeoRequest(Request $request): bool
    {
        return $request->attributes->has('__nfq_seo');
    }

    private function extractSeoDataFromRequest(Request $request): ?array
    {
        $seoData = $request->attributes->get('__nfq_seo');
        return empty($seoData) ? null : $seoData;
    }
}
