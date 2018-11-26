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
 * Class SeoTagNode
 * @package Nfq\SeoBundle\Twig
 */
class SeoTagNode extends \Twig_Node
{
    public function compile(\Twig_Compiler $compiler): void
    {
        $count = count($this->getNode('seo_node'));

        $compiler
            ->addDebugInfo($this);

        for ($i = 0; ($i < $count); $i++) {
            // argument is not an expression (such as, a \Twig_Node_Textbody)
            // we should trick with output buffering to get a valid argument to pass
            // to the functionToCall() function.
            if (!($this->getNode('seo_node')->getNode($i) instanceof \Twig_Node_Expression)) {
                $compiler
                    ->write('ob_start();')->raw(PHP_EOL)
                    ->subcompile($this->getNode('seo_node')->getNode($i))
                    ->write('$sT[] = ob_get_clean();')->raw(PHP_EOL);
            } else {
                $compiler
                    ->write('$sT[] = ')
                    ->subcompile($this->getNode('seo_node')->getNode($i))
                    ->raw(';')->raw(PHP_EOL);
            }
        }

        $compiler
            //Set seo method
            ->raw('$sM = ')->string('get')->raw(' . ucfirst($sT[1]);')->raw(PHP_EOL)
            //Set seo block
            ->raw('$sB = ')->raw('$sT[1];')->raw(PHP_EOL)
            //[1] holds tag name, we don't need that no more, so unsetting
            ->raw('unset($sT[1]);')->raw(PHP_EOL)
            //Try to render the block, and use that content instead of tag
            //This is used, for example, to override {% nfqseo title %} with {% block title %}
            ->raw('ob_start();')->raw(PHP_EOL)
            ->raw('$this->displayBlock($sB, $context, $blocks);')->raw(PHP_EOL)
            ->raw('$blockContent = ob_get_clean();')->raw(PHP_EOL)
            ->raw('$sE = $this->env->getExtension(')->string('nfq_seo')->raw(');')->raw(PHP_EOL)
            ->raw('if (empty($blockContent)) {')->raw(PHP_EOL)
            //[2] Holds predefined meta tags. Loop predefined blocks and render the content for them
            //This allows to override <meta name="foo" with {%block meta_foo %}
            ->raw('if (isset($sT[2])) {')->raw(PHP_EOL)
            ->raw('foreach($sT[2] as $predefinedName => $predefinedBlockName) {')
            ->raw('ob_start();')->raw(PHP_EOL)
            ->raw('$this->displayBlock($predefinedBlockName, $context, $blocks);')->raw(PHP_EOL)
            ->raw('$sT[2][$predefinedName] = ob_get_clean();')->raw(PHP_EOL)
            ->raw('}}')->raw(PHP_EOL)
            ->raw('echo call_user_func_array([$sE, $sM], array_filter($sT));')->raw(PHP_EOL)
            ->raw(' } else { ')->raw(PHP_EOL)
            ->raw('echo call_user_func_array([$sE, $sM], [$blockContent]); }')->raw(PHP_EOL)
            ->write('unset($sM, $sT, $sE, $sR, $sB);')->raw(PHP_EOL);
    }
}
