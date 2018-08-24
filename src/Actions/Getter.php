<?php
/**
 * Get data
 * User: moyo
 * Date: 22/12/2017
 * Time: 4:45 PM
 */

namespace Carno\Database\SQL\Actions;

use Carno\Database\Results\Selected;
use Carno\Database\SQL\Action;

trait Getter
{
    /**
     * @return array
     */
    public function get()
    {
        $this->actTrigger(Action::SELECT, $this);

        $this->limit(1);

        /**
         * @var Selected $rows
         */
        $rows = yield $this->exec($this->sql(Action::SELECT));
        if ($rows->count() > 0) {
            return (yield $this->rsTrigger((array)$rows))[0];
        }

        return [];
    }

    /**
     * @return array
     */
    public function list()
    {
        $this->actTrigger(Action::SELECT, $this);

        /**
         * @var Selected $rows
         */
        $rows = yield $this->exec($this->sql(Action::SELECT));
        if ($rows->count() > 0) {
            return yield $this->rsTrigger((array)$rows);
        }

        return [];
    }
}
