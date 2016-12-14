<?php
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
    /**
     * @var SeoInvalidatorManager
     */
    private $invalidatorManager;

    /**
     * @param SeoInvalidatorManager $si
     */
    public function __construct(SeoInvalidatorManager $invalidatorManager)
    {
        $this->invalidatorManager = $invalidatorManager;
    }

    /**
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            'postPersist',
            'postUpdate',
            'preRemove',
        ];
    }

    /**
     * @param LifecycleEventArgs $event
     */
    public function postPersist(LifecycleEventArgs $event)
    {
        $this->invalidate($event);
    }

    /**
     * @param LifecycleEventArgs $event
     */
    public function postUpdate(LifecycleEventArgs $event)
    {
        $this->invalidate($event);
    }

    /**
     * @param LifecycleEventArgs $event
     */
    public function preRemove(LifecycleEventArgs $event)
    {
        $this->remove($event);
    }

    /**
     * @param LifecycleEventArgs $event
     */
    private function remove(LifecycleEventArgs $event)
    {
        $entity = $event->getEntity();

        try {
            $invalidator = $this->getInvalidator($entity);

            $invalidator
                ->setEntityManager($event->getEntityManager())
                ->remove($entity);
        } catch (\InvalidArgumentException $ex) {
            //Invalidator was not found, so just continue
        }
    }
    
    /**
     * @param $event
     */
    private function invalidate(LifecycleEventArgs $event)
    {
        $entity = $event->getEntity();

        try {
            $invalidator = $this->getInvalidator($entity);

            $uow = $event->getEntityManager()->getUnitOfWork();

            $invalidator
                ->setEntityManager($event->getEntityManager())
                ->invalidate($entity, $uow->getEntityChangeSet($entity));
        } catch (\InvalidArgumentException $ex) {
            //Invalidator was not found, so just continue
        }
    }

    /**
     * @param $entity
     * @return SeoInvalidatorInterface
     */
    private function getInvalidator($entity)
    {
        $entityClass = get_class($entity);

        return $this->invalidatorManager->getInvalidator($entityClass);
    }
}
