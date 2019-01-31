<?php declare(strict_types=1);

/**
 * This file is part of the "NFQ Bundles" package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nfq\SeoBundle\Invalidator\Object;

use Doctrine\ORM\EntityManagerInterface;
use Nfq\SeoBundle\Entity\SeoInterface;

/**
 * Class InvalidationObject
 * @package Nfq\SeoBundle\Invalidator\Object
 */
abstract class InvalidationObject implements InvalidationObjectInterface
{
    /** @var EntityManagerInterface */
    private $em;

    /** @var object */
    private $entity;

    /** @var bool */
    private $hasChanges = false;

    /** @var string|null */
    private $locale;

    /** @var array */
    protected $changeSet = [];

    /**
     * Get an array of attribute names which should cause invalidation when modified.
     *
     * @return string[]
     */
    abstract protected function getInvalidationAttributes(): array;

    public function __construct(EntityManagerInterface $em, $entity, ?array $changeSet)
    {
        $this->em = $em;
        $this->entity = $entity;

        if (method_exists($entity, 'getLocale')) {
            $this->locale = $entity->getLocale();
        }

        $this->checkForChanges($changeSet);
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function getEntity()
    {
        return $this->entity;
    }

    public function hasChanges(): bool
    {
        return $this->hasChanges;
    }

    public function getInvalidationStatus(): int
    {
        return SeoInterface::STATUS_REDIRECT;
    }

    public function getJoinPart(): ?string
    {
        return null;
    }

    public function getWhereParamTypes(): array
    {
        return [];
    }

    protected function getEntityManager(): EntityManagerInterface
    {
        return $this->em;
    }

    /**
     * @param null|string[] $changeSet
     */
    protected function checkForChanges(?array $changeSet): void
    {
        if (null === $changeSet) {
            return;
        }

        $flippedInvalidationAttributes = array_flip($this->getInvalidationAttributes());

        if ($changedAttributes = array_intersect_key($changeSet, $flippedInvalidationAttributes)) {
            $this->hasChanges = true;
            $this->changeSet = $changeSet;
        }
    }
}
