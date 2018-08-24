<?php
/**
 * Related data merging
 * User: moyo
 * Date: 26/12/2017
 * Time: 10:33 AM
 */

namespace Carno\Database\SQL\Merging;

use Carno\Database\SQL\Builder;
use Carno\Database\SQL\Exception\RelatedMergingFailedException;
use Closure;

trait Related
{
    /**
     * similar with "relations" but merge single-data in source
     * @param string $table
     * @param string $bindKey
     * @param string $srcKey
     * @param Closure $userExe
     * @return Builder
     */
    public function relation(
        string $table,
        string $bindKey,
        string $srcKey = 'id',
        Closure $userExe = null
    ) : Builder {
        $this->rsRender($table, $bindKey, $srcKey, null, $userExe);

        return $this;
    }

    /**
     * similar with "relation" but attach listed-data in source
     * @param string $table
     * @param string $bindKey
     * @param string $listKey
     * @param string $srcKey
     * @param Closure $userExe
     * @return Builder
     */
    public function relations(
        string $table,
        string $bindKey,
        string $listKey,
        string $srcKey = 'id',
        Closure $userExe = null
    ) : Builder {
        $this->rsRender($table, $bindKey, $srcKey, $listKey, $userExe);

        return $this;
    }

    /**
     * @param string $table
     * @param string $bindKey
     * @param string $srcKey
     * @param string $listedAs
     * @param Closure $userExe
     */
    private function rsRender(
        string $table,
        string $bindKey,
        string $srcKey,
        string $listedAs = null,
        Closure $userExe = null
    ) : void {
        $this->rsWatching(function (array $rows) use ($table, $bindKey, $srcKey, $listedAs, $userExe) {

            $srcIds = [];

            array_walk($rows, function (array $row) use ($srcKey, &$srcIds) {
                if (isset($row[$srcKey])) {
                    $srcIds[] = $row[$srcKey];
                }
            });

            if (empty($srcIds)) {
                throw new RelatedMergingFailedException('Empty source identifies');
            }

            /**
             * @var Builder $builder
             */
            $builder = $this->new($table);

            if ($userExe) {
                $userExe($builder);
            }

            $related = yield $builder->where($bindKey, 'in', $srcIds)->list();

            $stack = [];
            foreach ($related as $row) {
                $stack[$row[$bindKey]][] = $row;
            }

            // merge single-data or attach listed-data
            foreach ($rows as $i => $row) {
                if ($listedAs) {
                    $rows[$i][$listedAs] = $stack[$row[$srcKey]] ?? [];
                } else {
                    $rows[$i] = array_merge(current($stack[$row[$srcKey]] ?? []) ?: [], $row);
                }
            }

            return $rows;
        });
    }
}
