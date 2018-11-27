<?php declare(strict_types=1);

/**
 * This file is part of the "NFQ Bundles" package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nfq\SeoBundle\Twig\Extension;

use Nfq\SeoBundle\Page\SeoPageInterface;
use Nfq\SeoBundle\Twig\SeoTagTokenParser;
use Nfq\SeoBundle\Utils\SeoHelper;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class SeoExtension
 * @package Nfq\SeoBundle\Twig\Extension
 */
class SeoExtension extends \Twig_Extension
{
    /** @var string */
    private $defaultLocale;

    /**  @var string */
    private $encoding;

    /** @var SeoPageInterface */
    private $sp;

    /** @var TranslatorInterface */
    private $translator;

    public function __construct(SeoPageInterface $sp, TranslatorInterface $translator)
    {
        $this->sp = $sp;
        $this->translator = $translator;
    }

    public function setDefaultLocale(string $defaultLocale): void
    {
        $this->defaultLocale = $defaultLocale;
    }

    public function setEncoding(string $encoding): void
    {
        $this->encoding = $encoding;
    }

    public function getTokenParsers(): array
    {
        return [
            new SeoTagTokenParser()
        ];
    }

    public function getTitle(string $title = null): string
    {
        if (empty($title)) {
            $title = $this->sp->getTitle();
            $titleExtras = $this->sp->getTitleExtras();

            if ($titleExtras) {
                $title = $this->translator->trans($title, $titleExtras);
            }
        }

        return sprintf('<title>%s</title>' . PHP_EOL, strip_tags($title));
    }

    public function getMetaTags(array $predefinedMetaTags = []): string
    {
        $html = '';

        //Filter the tags to avoid checking for empty values
        $predefinedMetaTags = array_filter($predefinedMetaTags);

        foreach ($this->sp->getMetas() as $type => $metas) {
            foreach ((array)$metas as $name => $meta) {
                if (isset($predefinedMetaTags[$name])) {
                    $content = $predefinedMetaTags[$name];
                    $extras = [];
                } else {
                    [$content, $extras] = $meta;
                }

                if ($content === false) {
                    continue;
                }

                $html .= !empty($content)
                    ? sprintf(
                        '<meta %s="%s" content="%s" />' . PHP_EOL,
                        $type,
                        $this->normalize($name),
                        $this->normalize($content, $extras)
                    )
                    : sprintf(
                        '<meta %s="%s" />' . PHP_EOL,
                        $type,
                        $this->normalize($name)
                    );
            }
        }

        return $html;
    }

    public function getHtmlAttributes(): string
    {
        $attributes = '';
        foreach ($this->sp->getHtmlAttributes() as $name => $value) {
            $attributes .= sprintf('%s="%s" ', $name, $value);
        }

        return rtrim($attributes);
    }

    public function getHeadAttributes(): string
    {
        $attributes = '';
        $merged = array_merge($this->getDefaultHeadAttributes(), $this->sp->getHeadAttributes());
        foreach ($merged as $name => $value) {
            $attributes .= sprintf('%s="%s" ', $name, $value);
        }

        return rtrim($attributes);
    }

    private function getDefaultHeadAttributes(): array
    {
        return [
            'lang' => SeoHelper::getLangFromLocale($this->sp->getLocale(), $this->defaultLocale),
        ];
    }

    public function getMetaLinks(): string
    {
        $html = '';

        $html .= $this->getLinkCanonical();
        $html .= $this->getLangAlternates();
        $html .= $this->getNextPrevRels();

        return $html;
    }

    private function getLinkCanonical(): string
    {
        $canonical = $this->sp->getLinkCanonical();

        if (!empty($canonical)) {
            return sprintf("<link rel=\"%s\" href=\"%s\" />\n",
                SeoPageInterface::SEO_REL_CANONICAL,
                $this->formatCanonicalUri($canonical)
            );
        }

        return '';
    }

    private function getLangAlternates(): string
    {
        $html = '';

        foreach ($this->sp->getLangAlternates() as $hrefLang => $uri) {
            $html .= sprintf(
                "<link rel=\"%s\" href=\"%s\" hreflang=\"%s\" />\n",
                SeoPageInterface::SEO_REL_ALTERNATE,
                $this->formatCanonicalUri($uri),
                $hrefLang
            );
        }

        return $html;
    }

    private function getNextPrevRels(): string
    {
        $html = '';
        $host = $this->sp->getHost();

        $prev = $this->sp->getLinkPrevPage();
        if (!empty($prev)) {
            $html .= sprintf("<link rel=\"%s\" href=\"%s\" />",
                SeoPageInterface::SEO_REL_PREV,
                $this->formatCanonicalUri($host . $prev)
            );
        }

        $next = $this->sp->getLinkNextPage();
        if (!empty($next)) {
            $html .= sprintf("<link rel=\"%s\" href=\"%s\" />",
                SeoPageInterface::SEO_REL_NEXT,
                $this->formatCanonicalUri($host . $next)
            );
        }

        return $html;
    }

    private function formatCanonicalUri(string $uri): string
    {
        $allowedQueryParams = $this->sp->getLinkOptions('allowed_canonical_parameters');
        $parsedQueryString = parse_url(rawurldecode($uri), PHP_URL_QUERY);

        if (!empty($allowedQueryParams) && !empty($parsedQueryString)) {
            $flippedParams = array_flip($allowedQueryParams);

            parse_str($parsedQueryString, $parsedQueryStringArr);
            $allowedQueryStringArr = array_intersect_key($parsedQueryStringArr, $flippedParams);

            [$uriPath,] = explode('?', $uri, 2);

            return SeoHelper::getUri($uriPath, $allowedQueryStringArr);
        }

        return $uri;
    }

    private function normalize(string $string, array $extras = []): string
    {
        if (isset($extras['translatable']) && $extras['translatable'] === true) {
            $string = $this->translator->trans($string, $extras);
        }

        $string = str_replace(
            ['%simple_host%', '%host%'],
            [$this->sp->getSimpleHost(), $this->sp->getHost()],
            $string
        );

        return htmlspecialchars(strip_tags($string), ENT_COMPAT, $this->encoding);
    }
}
