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

use Nfq\SeoBundle\Page\SeoPageInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class SeoPageDataSubscriber
 * @package Nfq\SeoBundle\EventListener
 */
class SeoPageDataSubscriber implements EventSubscriberInterface
{
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
        ];
    }

    public function onKernelController(FilterControllerEvent $event): void
    {
        $request = $event->getRequest();
        $controller = $event->getController();

        if (!\is_array($controller)
            || !$event->isMasterRequest()
            || $this->isFileRequest($request)
            || $this->isDebugRequest($request)) {
            return;
        }

        $this->setGenericSeoPageData($request);
    }

    private function setGenericSeoPageData(Request $request): void
    {
        $this->sp->setLocale($request->getLocale());
        $this->sp->setHost($request->getSchemeAndHttpHost());
        $this->sp->setSimpleHost('http://' . $request->getHost());

        $this->sp->addMeta('property', 'og:url', $this->sp->formatCanonicalUrl($request->getUri()));
        $this->sp->addMeta('property', 'og:type', 'website');

        if (!$this->isSeoRequest($request)) {
            return;
        }

        $seoData = $this->extractSeoDataFromRequest($request);

        if (null === $seoData) {
            return;
        }

        $fullUri = $this->getFullUrl($seoData['url'], $request->getQueryString());

        $this->sp->setLinkCanonical($fullUri);

        //Overwrite og:url with canonical url
        $this->sp->addMeta('property', 'og:url', $fullUri);
        $this->sp->setLangAlternates($seoData['alternates']);
    }

    private function getFullUrl(string $path, ?string $queryString): string
    {
        return $this->sp->formatCanonicalUrl($path . (!$queryString ? '' : '?' . $queryString));
    }

    private function isFileRequest(Request $request): bool
    {
        return (bool)preg_match('~\.[a-z0-9]{1,}$~', $request->getRequestUri());
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
