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
     * Where part by default has following query "su.route_name = :route_name AND su.locale = :locale"
     */
    public function getWherePart(): string;

    public function getJoinPart(): string;

    public function getWhereParams(): array;

    /**
     * @return object
     */
    public function getEntity();

    public function getLocale(): string;

    public function hasChanges(): bool;

    public function getInvalidationStatus(): int;
}
