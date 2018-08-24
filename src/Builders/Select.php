<?php
/**
 * Select fields
 * User: moyo
 * Date: 25/12/2017
 * Time: 12:17 PM
 */

namespace Carno\Database\SQL\Builders;

use Carno\Database\SQL\Builder;

trait Select
{
    /**
     * @var string
     */
    private $bSelect = '*';

    /**
     * @return string
     */
    protected function gSelect() : string
    {
        return $this->bSelect;
    }

    /**
     * @param array ...$fields
     * @return Builder
     */
    public function select(...$fields) : Builder
    {
        if (is_string($fields[0] ?? false)) {
            if (count($fields) === 1) {
                // select(expr)
                $this->bSelect = $fields[0];
            } else {
                // select(f1, f2)
                $selects = [];
                array_walk($fields, function (string $val) use (&$selects) {
                    $selects[] = is_numeric(strpos($val, ' ')) ? $val : sprintf('`%s`', $val);
                });
                $this->bSelect = implode(',', $selects);
            }
        }

        return $this;
    }
}
