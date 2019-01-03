<?php declare(strict_types=1);

/**
 * This file is part of the "NFQ Bundles" package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nfq\SeoBundle\Utils;

use Nfq\SeoBundle\Model\SeoSlug;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class SeoHelper
 * @package Nfq\SeoBundle\Utils
 */
class SeoHelper
{
    public static function isUrlAccessible(string $uri): bool
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

    public static function getUri(string $uri, ?array $params): string
    {
        if (isset($params) === true && !empty($params)) {
            $uri .= '?' . http_build_query($params, '', '&');
        }

        return $uri;
    }

    public static function parseQueryString(string $queryString): array
    {
        parse_str(html_entity_decode($queryString), $parsed);

        return $parsed;
    }
    
    /**
     * @param array|string $data
     */
    public static function generateHash($data): string
    {
        $data = \is_array($data) ? json_encode($data) : $data;
        return sprintf('%u', crc32(mb_strtolower($data, 'UTF-8')));
    }

    public static function buildStdUrl(array $hashData): string
    {
        $stdPath = $hashData['path'];
        unset($hashData['path']);

        $query = !empty($hashData) ? urldecode(http_build_query($hashData)) : '';
        return $stdPath . (!empty($query) ? '?' . $query : '');
    }

    public static function glueUrl(SeoSlug $seoSlug, string $pathSep, string $slugSep): string
    {
        $params = $seoSlug->getRouteParts();

        $needTransliteration = self::needTransliteration($seoSlug);

        //Urlize every param
        array_walk(
            $params,
            function (&$item) use ($slugSep, $needTransliteration) {
                if (\is_array($item)) {
                    $item = implode($slugSep, $item);
                }

                $item = mb_strtolower(trim((string)$item), 'UTF-8');

                $item = $needTransliteration
                    ? self::transliterate($item, $slugSep)
                    : self::urlize($item, $slugSep);
            }
        );

        return self::cleanUri($seoSlug->getPrefix() . $pathSep . implode($pathSep, $params), $pathSep);
    }

    public static function cleanUri(string $uri, string $pathSep): string
    {
        return preg_replace('~' . $pathSep . '+~', $pathSep, $uri);
    }

    public static function needTransliteration(SeoSlug $seoSlug): bool
    {
        $transliterationLangs = ['ru'];
        $seoLang = strtolower(self::getLangFromLocale($seoSlug->getLocale()));

        return \in_array($seoLang, $transliterationLangs, true);
    }

    public static function transliterate(string $url, string $slugSep): string
    {
        return Urlizer::transliterate($url, $slugSep);
    }

    public static function urlize(string $url, string $slugSep): string
    {
        return Urlizer::urlize($url, $slugSep);
    }

    public static function getLangFromLocale(string $locale, string $fallbackLocale = null): string
    {
        if (empty($locale) && $fallbackLocale) {
            $locale = $fallbackLocale;
        }
        
        [$lang,] = explode('_', $locale);
        return $lang;
    }

    public static function formatAlternateLocale(string $locale): string
    {
        return str_replace('_', '-', strtolower($locale));
    }

    public static function isCli(): bool
    {
        return PHP_SAPI === 'cli';
    }
}
