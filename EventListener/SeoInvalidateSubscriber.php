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
            'prePersist',
            'postUpdate',
            'preRemove',
        ];
    }

    public function prePersist(LifecycleEventArgs $event): void
    {
        $this->invalidate($event, __METHOD__);
    }

    public function postUpdate(LifecycleEventArgs $event): void
    {
        $this->invalidate($event, __METHOD__);
    }

    public function preRemove(LifecycleEventArgs $event): void
    {
        $this->remove($event, __METHOD__);
    }

    private function remove(LifecycleEventArgs $event, string $eventName): void
    {
        $entity = $event->getEntity();

        try {
            $invalidator = $this->getInvalidator($entity, $eventName);

            $invalidator->remove($entity);
        } catch (\InvalidArgumentException $ex) {
            //Invalidator was not found, so just continue
        }
    }

    private function invalidate(LifecycleEventArgs $event, string $eventName): void
    {
        $entity = $event->getEntity();

        try {
            $invalidator = $this->getInvalidator($entity, $eventName);

            $uow = $event->getEntityManager()->getUnitOfWork();

            $changeSet = $uow->getEntityChangeSet($entity);
            // If changeset is empty set it to null
            if (empty($changeSet)) {
                $changeSet = null;
            }

            $invalidator->invalidate($entity, $changeSet);
        } catch (\InvalidArgumentException $ex) {
            //Invalidator was not found, so just continue
        }
    }

    private function getInvalidator($entity, string $eventName): SeoInvalidatorInterface
    {
        return $this->invalidatorManager->getInvalidator(
            \get_class($entity),
            substr($eventName, strrpos($eventName, ':') + 1)
        );
    }
}
