<?php declare(strict_types=1);

/**
 * This file is part of the "NFQ Bundles" package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nfq\SeoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Nfq\SeoBundle\Entity\MappedSuperclass\Seo as SeoBase;

/**
 * @ORM\Table(name="seo_urls",
 *      indexes={
 *          @ORM\Index(name="route_locale_entity_index", columns={"route_name", "locale", "entity_id"}),
 *          @ORM\Index(name="std_path_hash_index", columns={"std_path_hash"}),
 *      }
 * )
 * @ORM\Entity()
 */
class Seo extends SeoBase implements SeoInterface
{

}
