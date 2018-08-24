<?php
/**
 * Rows limiting
 * User: moyo
 * Date: 25/12/2017
 * Time: 11:09 AM
 */

namespace Carno\Database\SQL\Builders;

use Carno\Database\SQL\Builder;

trait Limit
{
    /**
     * @var array
     */
    private $bLimit = [];

    /**
     * @return string
     */
    protected function gLimit() : string
    {
        return $this->bLimit ? sprintf(' LIMIT %d,%d', $this->bLimit[0], $this->bLimit[1]) : '';
    }

    /**
     * @param array ...$expr
     * @return Builder
     */
    public function limit(...$expr) : Builder
    {
        switch (count($expr)) {
            case 1:
                // limit(rows)
                $this->bLimit = [0, $expr[0]];
                break;
            case 2:
                // limit(offset, rows)
                $this->bLimit = $expr;
                break;
        }

        return $this;
    }
}
