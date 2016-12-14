<?php
/**
 * This file is part of the "NFQ Bundles" package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nfq\CronTrackBundle\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class ServiceCreationTest
 * @package Nfq\CronTrackBundle\Tests\Functional
 */
class ServiceCreationTest extends WebTestCase
{
    /**
     * Tests if container is returned
     */
    public function testGetContainer()
    {
        $container = self::createClient()->getKernel()->getContainer();
        $this->assertNotNull($container);
    }

    /**
     * Tests if service are created correctly.
     *
     * @param string $service
     * @param string $instance
     *
     * @dataProvider getTestServiceCreateData()
     */
    public function testServiceCreate($service = null, $instance = null)
    {
        if (!is_null($service)) {
            $container = self::createClient()->getKernel()->getContainer();
            $this->assertTrue($container->has($service), "Service `{$service}` is missing");

            if (!is_null($instance)) {
                $this->assertInstanceOf(
                    $instance,
                    $container->get($service),
                    "{$service} is not an instance of {$instance}"
                );
            }
        }
    }

    /**
     * Data provider for testServiceCreate().
     *
     * @return array
     */
    public function getTestServiceCreateData()
    {
        return [
            [
                'nfq_seo.router_subscriber',
                'Nfq\\SeoBundle\\EventSubscriber\\SeoRouterSubscriber',
            ],
            [
                'nfq_seo.invalidate_subscriber',
                'Nfq\\SeoBundle\\EventSubscriber\\SeoInvalidateSubscriber',
            ],
            [
                'nfq_seo.router',
                'Nfq\\SeoBundle\\Routing\\SeoRouter',
            ],
            [
                'nfq_seo.url_manager',
                'Nfq\\SeoBundle\\Service\\SeoManager',
            ],
            [
                'nfq_seo.alternates_manager',
                'Nfq\\SeoBundle\\Service\\AlternatesManager',
            ],
            [
                'nfq_seo.url_generator',
                'Nfq\\SeoBundle\\Generator\\SeoGeneratorManager',
            ],
            [
                'nfq_seo.url_invalidator',
                'Nfq\\SeoBundle\\Invalidator\\SeoInvalidator',
            ],
            [
                'nfq_seo.page.default',
                'Nfq\\SeoBundle\\Page\\SeoPage',
            ],
            [
                'nfq_seo.twig_extension',
                'Nfq\\SeoBundle\\Twig\\Extension\\SeoExtension',
            ],
            [
                'nfq_seo.exception_controller',
                'Nfq\\SeoBundle\\Controller\\ExceptionController',
            ],
        ];
    }
}