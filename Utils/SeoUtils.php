<?php declare(strict_types=1);

/**
 * This file is part of the "NFQ Bundles" package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nfq\SeoBundle\Utils;

/**
 * Class SeoUtils
 * @package Nfq\SeoBundle\Utils
 */
class SeoUtils
{
    /**
     * @param array $data
     * @param array $filter
     * @return mixed
     */
    public static function recursiveUnsetExisting(array $data, array $filter): array
    {
        $recursiveUnsetExisting = function (array $data, $filter, $deep = 0) use (&$recursiveUnsetExisting): array {
            foreach ($data as $k => $v) {
                if (\is_array($v)) {
                    $data[$k] = isset($filter[$k]) ? $recursiveUnsetExisting(
                        $data[$k],
                        $filter[$k],
                        $deep + 1
                    ) : $data[$k];
                } else {
                    if (isset($filter[$k])) {
                        $data[$k] = null;
                    }
                }
            }

            return array_filter($data);
        };

        return $recursiveUnsetExisting($data, $filter);
    }

    /**
     * http://php.net/array_diff_key recursive implementation.
     *
     * @credits https://github.com/gajus/marray
     * @TODO Support variadic input.
     * @param array $arr1 The array with master keys to check.
     * @param array $arr2 An array to compare keys against.
     * @return array
     */
    public static function diffKeyRecursive(array $arr1, array $arr2): array
    {
        $diffKeyRecursive = function (array $arr1, array $arr2) use (&$diffKeyRecursive): array {
            $diff = array_diff_key($arr1, $arr2);
            $intersect = array_intersect_key($arr1, $arr2);

            foreach ($intersect as $k => $v) {
                if (\is_array($arr1[$k]) && \is_array($arr2[$k])) {
                    $d = $diffKeyRecursive($arr1[$k], $arr2[$k]);

                    if ($d) {
                        $diff[$k] = $d;
                    }
                }
            }

            return $diff;
        };

        return $diffKeyRecursive($arr1, $arr2);
    }
}
