<?php
/**
 * Delete data
 * User: moyo
 * Date: 25/12/2017
 * Time: 3:27 PM
 */

namespace Carno\Database\SQL\Actions;

use Carno\Database\Results\Updated;
use Carno\Database\SQL\Action;

trait Delete
{
    /**
     * @param array ...$conditions
     * @return int
     */
    public function delete(...$conditions)
    {
        $this->actTrigger(Action::DELETE, $this);

        $conditions && $this->where(...$conditions);

        /**
         * @var Updated $result
         */
        $result = yield $this->exec($this->sql(Action::DELETE));

        return $result->rows();
    }
}
