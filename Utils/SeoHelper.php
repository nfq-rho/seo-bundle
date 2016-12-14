<?php
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

/**
 * Class SeoHelper
 * @package Nfq\SeoBundle\Utils
 */
class SeoHelper
{
    /**
     * @param string $uri
     * @param array $params
     * @return string
     */
    public static function getUri($uri, $params)
    {
        if (isset($params) === true && !empty($params)) {
            $uri .= '?' . http_build_query($params, '', '&');
        }

        return $uri;
    }

    /**
     * @param string $queryString
     * @return array
     */
    public static function parseQueryString($queryString)
    {
        parse_str(html_entity_decode($queryString), $parsed);

        return $parsed;
    }
    
    /**
     * @param array|string $data
     * @return string
     */
    public static function generateHash($data)
    {
        $data = is_array($data) ? json_encode($data) : $data;
        return sprintf('%u', crc32(mb_strtolower($data, 'UTF-8')));
    }

    /**
     * @param array $hashData
     * @return string
     */
    public static function buildStdUrl(array $hashData)
    {
        $stdPath = $hashData['path'];
        unset($hashData['path']);

        $query = !empty($hashData) ? urldecode(http_build_query($hashData)) : '';
        return $stdPath . (!empty($query) ? '?' . $query : '');
    }

    /**
     * @param SeoSlug $seoSlug
     * @param string $pathSep
     * @param string $slugSep
     * @return string
     */
    public static function glueUrl(SeoSlug $seoSlug, $pathSep, $slugSep)
    {
        $params = $seoSlug->getRouteParts();

        $needTransliteration = self::needTransliteration($seoSlug);

        //Urlize every param
        array_walk(
            $params,
            function (&$item) use ($slugSep, $needTransliteration) {
                if (is_array($item)) {
                    $item = implode($slugSep, $item);
                }

                $item = mb_strtolower(trim($item), 'UTF-8');

                $item = $needTransliteration
                    ? self::transliterate($item, $slugSep)
                    : self::urlize($item, $slugSep);
            }
        );

        return self::cleanUri($seoSlug->getPrefix() . $pathSep . implode($pathSep, $params), $pathSep);
    }

    /**
     * @param string $uri
     * @param string $pathSep
     * @return string
     */
    public static function cleanUri($uri, $pathSep)
    {
        return preg_replace('~' . $pathSep . '+~', $pathSep, $uri);
    }

    /**
     * @param SeoSlug $seoSlug
     * @return bool
     */
    public static function needTransliteration(SeoSlug $seoSlug)
    {
        $transliterationLangs = ['ru'];
        $seoLang = strtolower(self::getLangFromLocale($seoSlug->getLocale()));

        return (in_array($seoLang, $transliterationLangs));
    }

    /**
     * @param string $url
     * @param string $slugSep
     * @return string
     */
    public static function transliterate($url, $slugSep)
    {
        return Urlizer::transliterate($url, $slugSep);
    }

    /**
     * @param string $url
     * @param string $slugSep
     * @return string
     */
    public static function urlize($url, $slugSep)
    {
        return Urlizer::urlize($url, $slugSep);
    }

    /**
     * @param string $locale
     * @param bool $fallbackLocale
     * @return string
     */
    public static function getLangFromLocale($locale, $fallbackLocale = false)
    {
        if (empty($locale) && $fallbackLocale) {
            $locale = $fallbackLocale;
        }
        
        list($lang,) = explode('_', $locale);
        return $lang;
    }

    /**
     * @param string $locale
     * @return string
     */
    public static function formatAlternateLocale($locale)
    {
        return str_replace('_', '-', strtolower($locale));
    }

    /**
     * @return bool
     */
    public static function isCli()
    {
        return PHP_SAPI === 'cli';
    }
}
