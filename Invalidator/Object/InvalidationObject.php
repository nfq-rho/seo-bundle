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

    /** @var array */
    protected $changeSet = [];

    /** @var bool */
    private $hasChanges = false;

    /** @var string|null */
    private $locale;

    /**
     * Get an array of attribute names which should cause invalidation when modified.
     *
     * @return string[]
     */
    abstract protected function getInvalidationAttributes(): array;

    public function __construct(EntityManagerInterface $em, $entity, array $changeSet)
    {
        $this->em = $em;
        $this->changeSet = $changeSet;
        $this->entity = $entity;

        if (method_exists($entity, 'getLocale')) {
            $this->locale = $entity->getLocale();
        }

        $this->checkForChanges();
    }

    public function getWhereParam(string $paramName)
    {
        $params = $this->getWhereParams();
        return $params[$paramName] ?? null;
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

    protected function getEntityManager(): EntityManagerInterface
    {
        return $this->em;
    }

    protected function checkForChanges(): void
    {
        $flippedInvalidationAttributes = array_flip($this->getInvalidationAttributes());

        if ($changedAttributes = array_intersect_key($this->changeSet, $flippedInvalidationAttributes)) {
            $this->hasChanges = true;
        }
    }
}
