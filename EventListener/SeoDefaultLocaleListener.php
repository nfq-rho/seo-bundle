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

    public function __construct($defaultLocale)
    {
        $this->defaultLocale = $defaultLocale;
    }

    public function onKernelRequest(GetResponseEvent $event): void
    {
        if (empty($this->defaultLocale)) {
            return;
        }

        $request = $event->getRequest();
        $this->setLocale($request);
    }

    private function setLocale(Request $request): void
    {
        $request->setDefaultLocale($this->defaultLocale);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 33],
        ];
    }
}
