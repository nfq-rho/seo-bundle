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

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class SegmentBasedNotFoundResolver
 * @package Nfq\SeoBundle\Routing
 */
class SegmentBasedNotFoundResolver implements NotFoundResolverInterface
{
    /**
     * Tries to resolve a working url by removing one url segment at a time and checking
     * the response of new URL using HEAD request. Does not cause chain of redirects. Eg
     *
     * /segment-1/segment2/segment-3/ -> return 404, remove /
     * /segment-1/segment2/segment-3 -> return 404, remove /segment-3
     * /segment-1/segment2 -> return 404, remove /segment2
     * /segment-1 -> return 200, 301, 302, 307 or 308 OK this is the correct url redirect to it
     *
     * @param Request $request
     * @return string
     */
    public function resolve(Request $request): string
    {
        $failedPath = $request->getPathInfo();

        do {
            $newPath = substr($failedPath, 0, strrpos($failedPath, '/'));
            $newUri = $this->getFullUri($request, $newPath);
            $failedPath = $newPath;
        } while (!empty($newPath) && !$this->isUrlAccessible($newUri));

        return $newUri;
    }

    private function isUrlAccessible(string $uri): bool
    {
        $context = stream_context_create([
            'http' => [
                'method' => 'HEAD'
            ]
        ]);

        $headers = @get_headers($uri, 1, $context);
        if (!$headers || !preg_match('#HTTP/\d+\.\d+ (?P<response_code>\d+)#', $headers[0], $matches)) {
            return false;
        }

        return in_array(
            (int)$matches['response_code'],
            [
                Response::HTTP_OK,
                Response::HTTP_MOVED_PERMANENTLY,
                Response::HTTP_FOUND,
                Response::HTTP_TEMPORARY_REDIRECT,
                Response::HTTP_PERMANENTLY_REDIRECT,
            ],
            true
        );
    }

    private function getFullUri(Request $request, string $path): string
    {
        $qs = $request->getQueryString();
        return $request->getUriForPath($path) . ($qs ? '?' . $qs : '');
    }
}
