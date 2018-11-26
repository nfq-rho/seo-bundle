<?php declare(strict_types=1);

/**
 * This file is part of the "NFQ Bundles" package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nfq\SeoBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\FlattenException;
use Symfony\Component\HttpKernel\Log\DebugLoggerInterface;

/**
 * This controller throws correct exception when trying to access invalid SEO url
 *
 * Class ExceptionController
 * @package Nfq\SeoBundle\Controller
 */
class ExceptionController extends \Symfony\Bundle\TwigBundle\Controller\ExceptionController
{
    /**
     * @param Request $request
     * @param FlattenException $exception
     * @param DebugLoggerInterface $logger
     * @return Response|void
     */
    public function seoShowAction(Request $request, $exception, DebugLoggerInterface $logger = null)
    {
        $response = parent::showAction($request, FlattenException::create($exception), $logger);
        $response->setStatusCode(404);
        return $response;
    }
}
