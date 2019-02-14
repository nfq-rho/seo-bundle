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
use Nfq\SeoBundle\Service\AlternatesManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
    /** @var AlternatesManager */
    private $alternatesManager;

    /** @var SeoPageInterface */
    private $seoPage;


    public function __construct(AlternatesManager $alternatesManager, SeoPageInterface $seoPage)
    {
        $this->alternatesManager = $alternatesManager;
        $this->seoPage = $seoPage;
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

        $this->setGenericSeoPageData($request, $controller[0]);
    }

    private function setGenericSeoPageData(Request $request, AbstractController $controller): void
    {
        $this->seoPage->setLocale($request->getLocale());
        $this->seoPage->setHost($request->getSchemeAndHttpHost());
        $this->seoPage->setSimpleHost('http://' . $request->getHost());

        $this->seoPage->addMeta('property', 'og:url', $this->seoPage->formatCanonicalUrl($request->getUri()));
        $this->seoPage->addMeta('property', 'og:type', 'website');

        if ($controller instanceof SeoAwareControllerInterface) {
            $this->seoPage->setLangAlternates($this->alternatesManager->getRegularUrlLangAlternates($request));
        }

        if (!$this->isSeoRequest($request)) {
            return;
        }

        $seoData = $this->extractSeoDataFromRequest($request);

        if (null === $seoData) {
            $this->seoPage->setLangAlternates($this->alternatesManager->getRegularUrlLangAlternates($request));
            return;
        }

        $fullUri = $this->getFullUrl($seoData['url'], $request->getQueryString());

        $this->seoPage->setLinkCanonical($fullUri);

        //Overwrite og:url with canonical url
        $this->seoPage->addMeta('property', 'og:url', $fullUri);
        $this->seoPage->setLangAlternates($seoData['alternates']);
    }

    private function getFullUrl(string $path, ?string $queryString): string
    {
        return $this->seoPage->formatCanonicalUrl($path . (!$queryString ? '' : '?' . $queryString));
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
