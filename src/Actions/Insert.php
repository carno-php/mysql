<?php
/**
 * Insert data
 * User: moyo
 * Date: 25/12/2017
 * Time: 3:11 PM
 */

namespace Carno\Database\SQL\Actions;

use Carno\Database\Results\Created;
use Carno\Database\SQL\Action;

trait Insert
{
    /**
     * @param array ...$maps
     * @return int
     */
    public function insert(...$maps)
    {
        $this->actTrigger(Action::INSERT, $this);

        $maps && $this->data(...$maps);

        /**
         * @var Created $result
         */
        return
            ($result = yield $this->exec($this->sql(Action::INSERT))) instanceof Created
                ? $result->id()
                : 0
        ;
    }
}
