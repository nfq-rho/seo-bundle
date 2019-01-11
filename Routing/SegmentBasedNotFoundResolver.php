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

use Nfq\SeoBundle\Utils\SeoHelper;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class SegmentBasedNotFoundResolver
 * @package Nfq\SeoBundle\Routing
 */
class SegmentBasedNotFoundResolver implements NotFoundResolverInterface
{
    /**
     * Tries to resolve a matched url by removing one url segment at a time and checking
     * the response of new url using HEAD request. Does not cause chain of redirects. Eg
     *
     * /segment-1/segment2/segment-3/ -> return 404, remove /
     * /segment-1/segment2/segment-3 -> return 404, remove /segment-3
     * /segment-1/segment2 -> return 404, remove /segment2
     * /segment-1 -> return 200, 301, 302, 307 or 308 OK this is the correct url return it
     */
    public function resolve(Request $request): ?string
    {
        $failedPath = $request->getPathInfo();
        $failedUrl = $request->getUri();

        do {
            $newPath = substr($failedPath, 0, strrpos($failedPath, '/'));
            $newUrl = $this->getFullUri($request, $newPath);
            $failedPath = $newPath;
        } while (!empty($newPath) && null === $accessibleUrl = $this->getAccessibleUrl($newUrl, $failedUrl));

        return $accessibleUrl;
    }

    private function getFullUri(Request $request, string $path): string
    {
        $qs = $request->getQueryString();
        return $request->getUriForPath($path) . ($qs ? '?' . $qs : '');
    }

    private function getAccessibleUrl(string $newUrl, string $failedUrl): ?string
    {
        $accessibleUrl = SeoHelper::getAccessibleUrl($newUrl);

        // Prevent redirect loop if failed url is same as new accessible url
        if ($failedUrl === $accessibleUrl) {
            return null;
        }

        return $accessibleUrl;
    }
}
