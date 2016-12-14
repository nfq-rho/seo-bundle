<?php
/**
 * This file is part of the "NFQ Bundles" package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nfq\SeoBundle\Invalidator\Object;

use Nfq\SeoBundle\Entity\SeoInterface;

/**
 * Class InvalidationObject
 * @package Nfq\SeoBundle\Invalidator\Object
 */
abstract class InvalidationObject implements InvalidationObjectInterface
{
    /**
     * @var array
     */
    protected $changeSet = [];

    /**
     * @var bool
     */
    private $hasChanges = false;

    /**
     * @var object
     */
    private $entity;

    /**
     * @var string|null
     */
    protected $locale = null;

    /**
     * Get an array of attribute names which should cause invalidation when modified.
     *
     * @return array
     */
    abstract protected function getInvalidationAttributes();

    /**
     * InvalidationObject constructor.
     * @param object $entity
     * @param array $changeSet
     */
    public function __construct($entity, array $changeSet)
    {
        $this->changeSet = $changeSet;
        $this->entity = $entity;

        if (method_exists($entity, 'getLocale')) {
            $this->locale = $entity->getLocale();
        }

        $this->checkForChanges();
    }

    /**
     * @inheritdoc
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @inheritdoc
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @inheritdoc
     */
    public function hasChanges()
    {
        return $this->hasChanges;
    }

    /**
     * @inheritdoc
     */
    public function getInvalidationStatus()
    {
        return SeoInterface::STATUS_REDIRECT;
    }

    /**
     * @inheritdoc
     */
    protected function checkForChanges()
    {
        $flippedInvalidationAttributes = array_flip($this->getInvalidationAttributes());

        if ($changedAttributes = array_intersect_key($this->changeSet, $flippedInvalidationAttributes)) {
            $this->hasChanges = true;
        }
    }
}
