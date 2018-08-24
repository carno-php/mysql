<?php
/**
 * Timestamps of [Created|Updated]
 * User: moyo
 * Date: 25/12/2017
 * Time: 5:47 PM
 */

namespace Carno\Database\SQL\Features;

use Carno\Database\SQL\Action;
use Carno\Database\SQL\Builder;

trait Timestamps
{
    /**
     * @return array [[ACTION, Closure $program]]
     */
    protected function timestampsChip() : array
    {
        $createdF = $this->createdAt ?? 'createdAt';
        $updatedF = $this->updatedAt ?? 'updatedAt';

        return [
            [
                Action::INSERT,
                function (Builder $builder) use ($createdF, $updatedF) {
                    $builder->data([
                        $createdF => time(),
                        $updatedF => time(),
                    ]);
                }
            ],
            [
                Action::UPDATE,
                function (Builder $builder) use ($updatedF) {
                    $builder->data([
                        $updatedF => time(),
                    ]);
                }
            ],
        ];
    }
}
