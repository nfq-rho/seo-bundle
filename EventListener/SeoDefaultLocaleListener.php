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

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class SeoDefaultLocaleListener
 * @package Nfq\SeoBundle\EventListener
 */
class SeoDefaultLocaleListener implements EventSubscriberInterface
{
    /** @var string */
    protected $defaultLocale;

    public function __construct(string $defaultLocale)
    {
        $this->defaultLocale = $defaultLocale;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 33],
        ];
    }

    public function onKernelRequest(GetResponseEvent $event): void
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $request = $event->getRequest();
        $pathInfo = $request->getPathInfo();

        $request->setDefaultLocale($this->defaultLocale);

        if (null !== $locale = $this->extractLocaleFromPath($pathInfo)) {
            $request->setDefaultLocale($locale);
        }
    }

    private function extractLocaleFromPath(string $pathInfo): ?string
    {
        if (preg_match('/^\/([a-z]{2})\/.*/', $pathInfo, $matches)) {
            return $matches[1];
        }

        return null;
    }
}
