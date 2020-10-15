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

/**
 * Interface InvalidationObjectInterface
 * @package Nfq\SeoBundle\Invalidator\Object
 */
interface InvalidationObjectInterface
{
    /**
     * Where part by default has following query "su.route_names IN (:route_names) AND su.locale = :locale"
     */
    public function getWherePart(): ?string;

    public function getJoinPart(): ?string;

    /**
     * @return string[]
     */
    public function getWhereParams(): array;

    /**
     * Specify custom param types. For example if parameter holds an array, it's type has to be specified
     * @return string[]
     */
    public function getWhereParamTypes(): array;

    /**
     * @return object
     */
    public function getEntity();

    public function getLocale(): ?string;

    public function hasChanges(): bool;

    public function getInvalidationStatus(): int;
}
