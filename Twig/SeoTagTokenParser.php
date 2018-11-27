<?php declare(strict_types=1);

/**
 * This file is part of the "NFQ Bundles" package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nfq\SeoBundle\Twig;

/**
 * Class SeoTagTokenParser
 * @package Nfq\SeoBundle\Twig
 */
class SeoTagTokenParser extends \Twig_TokenParser
{
    private $tagOpen = 'nfqseo';

    private $tagClose = 'endnfqseo';

    public function parse(\Twig_Token $token): \Twig_Node
    {
        $lineNo = $token->getLine();
        $stream = $this->parser->getStream();

        // recovers all inline parameters close to your tag name
        $params = array_merge(array(), $this->getInlineParams($token));

        $continue = true;
        while ($continue) {
            // create subtree until the decideMyTagFork() callback returns true
            $body = $this->parser->subparse(array($this, 'decideNfqSeoTagFork'));

            // I like to put a switch here, in case you need to add middle tags, such
            // as: {% mytag %}, {% nextmytag %}, {% endmytag %}.
            $tag = $stream->next()->getValue();

            switch ($tag) {
                case $this->tagClose:
                    $continue = false;
                    break;
                default:
                    throw new \Twig_Error_Syntax(
                        sprintf(
                            'Unexpected end of template. Twig was looking for the following tags "%s" to close the "%s" block started at line %d)',
                            $this->tagClose,
                            $this->tagOpen,
                            $lineNo
                        ), -1
                    );
            }

            // you want $body at the beginning of your arguments
            array_unshift($params, $body);

            // if your tag can also contains params, you can uncomment this line:
            // $params = array_merge($params, $this->getInlineParams($token));
            // and comment this one:
            $stream->expect(\Twig_Token::BLOCK_END_TYPE);
        }

        return new SeoTagNode(['seo_node' => new \Twig_Node($params)], [], $lineNo, $this->getTag());
    }

    /**
     * Recovers all tag parameters until we find a BLOCK_END_TYPE ( %} )
     *
     * @param \Twig_Token $token
     * @return array
     */
    protected function getInlineParams(\Twig_Token $token): array
    {
        $stream = $this->parser->getStream();
        $params = array();
        while (!$stream->test(\Twig_Token::BLOCK_END_TYPE)) {
            $params[] = $this->parser->getExpressionParser()->parseExpression();
        }
        $stream->expect(\Twig_Token::BLOCK_END_TYPE);
        return $params;
    }

    /**
     * Callback called at each tag name when subparsing, must return
     * true when the expected end tag is reached.
     *
     * @param \Twig_Token $token
     * @return bool
     */
    public function decideNfqSeoTagFork(\Twig_Token $token): bool
    {
        return $token->test(array($this->tagClose));
    }

    public function getTag(): string
    {
        return $this->tagOpen;
    }
}
