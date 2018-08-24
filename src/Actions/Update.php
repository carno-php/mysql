<?php
/**
 * Update data
 * User: moyo
 * Date: 25/12/2017
 * Time: 3:24 PM
 */

namespace Carno\Database\SQL\Actions;

use Carno\Database\Results\Updated;
use Carno\Database\SQL\Action;

trait Update
{
    /**
     * @param array ...$maps
     * @return int
     */
    public function update(...$maps)
    {
        $this->actTrigger(Action::UPDATE, $this);

        $maps && $this->data(...$maps);

        /**
         * @var Updated $result
         */
        $result = yield $this->exec($this->sql(Action::UPDATE));

        return $result->rows();
    }
}
