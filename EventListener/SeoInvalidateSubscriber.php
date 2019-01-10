<?php declare(strict_types=1);

/**
 * This file is part of the "NFQ Bundles" package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nfq\SeoBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Nfq\SeoBundle\Invalidator\SeoInvalidatorInterface;
use Nfq\SeoBundle\Invalidator\SeoInvalidatorManager;

/**
 * Class SeoInvalidateSubscriber
 * @package Nfq\SeoBundle\EventListener
 */
class SeoInvalidateSubscriber implements EventSubscriber
{
    /** @var SeoInvalidatorManager */
    private $invalidatorManager;

    public function __construct(SeoInvalidatorManager $invalidatorManager)
    {
        $this->invalidatorManager = $invalidatorManager;
    }

    public function getSubscribedEvents(): array
    {
        return [
            'postUpdate',
            'preRemove',
        ];
    }

    public function postUpdate(LifecycleEventArgs $event): void
    {
        $this->invalidate($event);
    }

    public function preRemove(LifecycleEventArgs $event): void
    {
        $this->remove($event);
    }

    private function remove(LifecycleEventArgs $event): void
    {
        $entity = $event->getEntity();

        try {
            $invalidator = $this->getInvalidator($entity);

            $invalidator->remove($entity);
        } catch (\InvalidArgumentException $ex) {
            //Invalidator was not found, so just continue
        }
    }
    
    private function invalidate(LifecycleEventArgs $event): void
    {
        $entity = $event->getEntity();

        try {
            $invalidator = $this->getInvalidator($entity);

            $uow = $event->getEntityManager()->getUnitOfWork();

            $invalidator->invalidate($entity, $uow->getEntityChangeSet($entity));
        } catch (\InvalidArgumentException $ex) {
            //Invalidator was not found, so just continue
        }
    }

    private function getInvalidator($entity): SeoInvalidatorInterface
    {
        return $this->invalidatorManager->getInvalidator(\get_class($entity));
    }
}
