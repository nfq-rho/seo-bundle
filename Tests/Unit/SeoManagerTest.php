<?php
/**
 * This file is part of the "NFQ Bundles" package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nfq\SeoBundle\Tests\Unit;

use Doctrine\ORM\EntityManager;
use Mockery as m;
use Nfq\SeoBundle\Entity\Seo;
use Nfq\SeoBundle\Entity\SeoInterface;
use Nfq\SeoBundle\Repository\SeoRepository;
use Nfq\SeoBundle\Generator\SeoGeneratorManager;
use Nfq\SeoBundle\Model\SeoSlug;
use Nfq\SeoBundle\Service\SeoManager;

/**
 * Class SeoManagerTest
 * @package Nfq\SeoBundle\Tests\Unit
 */
class SeoManagerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        global $test_IT;
        $test_IT = 0;
        parent::setUp();
    }

    public function tearDown()
    {
        global $test_IT;
        parent::tearDown();
        $test_IT = 0;
        m::close();
    }

    /**
     * @return array
     */
    public function urlSet()
    {
        return [
            [
                'hashData' => [
                    'path' => '/lt_LT/some-slug/acode_1/bcode_1/1',
                ],
                'seoSlug' => (new SeoSlug())
                    ->setRouteName('route_name_a')
                    ->setEntityId(1)
                    ->setPrefix('/lt_LT/some-slug')
                    ->setLocale('lt_LT')
                    ->setRouteParts([
                        'group_1' => [
                            'acode_1' => 'A code 1 value',
                            'bcode_1' => 'B code 1 value',
                        ],
                        'group_2' => 'Foo Bar'
                    ]),
                'whiles' => [
                    //First iteration of while
                    [
                        'existing' => (new Seo())
                            ->setSeoPathHash('2568327977')
                            ->setStdPathHash('2644116147')
                            ->setLocale('lt_LT')
                            ->setRouteName('route_name_a')
                            ->setEntityId(1)
                            ->setSeoUrl('/lt_LT/some-slug/a-code-1-value-b-code-1-value/foo-bar')
                            ->setStdUrl('/lt_LT/some-slug/acode_1/bcode_1/1')
                            ->setStatus(SeoInterface::STATUS_OK),
                        'new' => (new Seo())
                            ->setSeoPathHash('2568327977')
                            ->setStdPathHash('2644116147')
                            ->setLocale('lt_LT')
                            ->setRouteName('route_name_a')
                            ->setEntityId(1)
                            ->setSeoUrl('/lt_LT/some-slug/a-code-1-value-b-code-1-value/foo-bar')
                            ->setStdUrl('/lt_LT/some-slug/acode_1/bcode_1/1')
                            ->setStatus(SeoInterface::STATUS_OK),
                    ],
                    //Second iteration of while
                    [
                        'existing' => (new Seo())
                            ->setSeoPathHash('2221202832')
                            ->setStdPathHash('2644116147')
                            ->setLocale('lt_LT')
                            ->setRouteName('route_name_a')
                            ->setEntityId(1)
                            ->setSeoUrl('/lt_LT/some-slug/a-code-1-value-b-code-1-value/foo-bar-2')
                            ->setStdUrl('/lt_LT/some-slug/acode_1/bcode_1/1')
                            ->setStatus(SeoInterface::STATUS_OK),
                        'new' => (new Seo())
                            ->setSeoPathHash('2221202832')
                            ->setStdPathHash('2644116147')
                            ->setLocale('lt_LT')
                            ->setRouteName('route_name_a')
                            ->setEntityId(1)
                            ->setSeoUrl('/lt_LT/some-slug/a-code-1-value-b-code-1-value/foo-bar-2')
                            ->setStdUrl('/lt_LT/some-slug/acode_1/bcode_1/1')
                            ->setStatus(SeoInterface::STATUS_OK)
                    ],
                    //Third iteration of while
                    [
                        'new' => (new Seo())
                            ->setSeoPathHash('4083412230')
                            ->setStdPathHash('2644116147')
                            ->setLocale('lt_LT')
                            ->setRouteName('route_name_a')
                            ->setEntityId(1)
                            ->setSeoUrl('/lt_LT/some-slug/a-code-1-value-b-code-1-value/foo-bar-3')
                            ->setStdUrl('/lt_LT/some-slug/acode_1/bcode_1/1')
                            ->setStatus(SeoInterface::STATUS_OK),
                    ]
                ],
                'expectedSeo' => '/lt_LT/some-slug/a-code-1-value-b-code-1-value/foo-bar-3',
            ],
        ];
    }

    /**
     * @dataProvider urlSet
     */
    public function testIfUniqueUrlIsCreatedCorrectly($hashData, $seoSlug, $whiles, $expectedSeo)
    {
        $uniqueClosure = function (Seo $data) use ($whiles) {
            global $test_IT;

            if (!isset($whiles[$test_IT]['new'])) {
                return true;
            }

            /** @var Seo $testCase */
            $testCase = $whiles[$test_IT]['new'];

            $test_IT++;

            return ($data->getSeoPathHash() == $testCase->getSeoPathHash()
                && $data->getStdPathHash() == $testCase->getStdPathHash());
        };

        $generatorMock = $this->getGeneratorMock($hashData, $seoSlug);

        $generatorManagerMock = $this->getGeneratorManagerMock();
        $generatorManagerMock->shouldReceive('getGenerator')->andReturn($generatorMock);

        $repoMock = $this->getRepositoryMock();
        $repoMock
            ->shouldReceive('isUnique')
            ->with(M::on($uniqueClosure))
            ->andReturnUsing(function () use ($whiles) {
                global $test_IT;

                $test_IT_before = $test_IT - 1;

                return isset($whiles[$test_IT_before]['existing']) ? $whiles[$test_IT_before]['existing'] : null;
            });

        $repoMock->shouldReceive('save')->andReturnUndefined();

        $emMock = $this->getEntityManagerMock();
        $emMock->shouldReceive('getRepository')->andReturn($repoMock);

        $sm = new SeoManager($generatorManagerMock, $emMock);
        $sm->setSlugSeparator('-');
        $sm->setPathSeparator('/');

        $newSeoUrl = $sm->createSeoUrl('some_name', ['some_params' => []]);

        $this->assertInstanceOf('Nfq\\SeoBundle\\Entity\\Seo', $newSeoUrl);
        $this->assertEquals($expectedSeo, $newSeoUrl->getSeoUrl());
    }

    /**
     * @return m\MockInterface
     */
    private function getGeneratorMock($hashData, $seoSlug)
    {
        $mock = m::mock('Nfq\\SeoBundle\\Generator\\GeneratorInterface');

        $mock->shouldReceive('getHashData')->andReturn($hashData);
        $mock->shouldReceive('generate')->andReturn($seoSlug);

        return $mock;
    }

    /**
     * @return m\MockInterface|SeoGeneratorManager
     */
    private function getGeneratorManagerMock()
    {
        $mock = m::mock('Nfq\\SeoBundle\\Generator\\SeoGeneratorManager');

        return $mock;
    }

    /**
     * @return m\MockInterface|SeoRepository
     */
    private function getRepositoryMock()
    {
        $mock = m::mock('Nfq\\SeoBundle\\Entity\\SeoRepository');
        $mock->shouldDeferMissing();

        $mock->shouldReceive('createNew')->andReturn(new Seo());

        return $mock;
    }


    /**
     * @return m\MockInterface|EntityManager
     */
    private function getEntityManagerMock()
    {
        $mock = m::mock('Doctrine\\ORM\\EntityManager');
        $mock->shouldDeferMissing();

        return $mock;
    }
}
