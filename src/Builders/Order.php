<?php
/**
 * Columns orders
 * User: moyo
 * Date: 25/12/2017
 * Time: 11:09 AM
 */

namespace Carno\Database\SQL\Builders;

use Carno\Database\SQL\Builder;

trait Order
{
    /**
     * @var array
     */
    private $bOrders = [];

    /**
     * @return string
     */
    protected function gOrders() : string
    {
        return $this->bOrders ? sprintf(' ORDER BY %s', implode(',', $this->bOrders)) : '';
    }

    /**
     * @param array ...$orders
     * @return Builder
     */
    public function order(...$orders) : Builder
    {
        if (is_array($orders[0] ?? false)) {
            // order([expr1], [expr2])
            foreach ($orders as $order) {
                $this->order(...$order);
            }
        } else {
            switch (count($orders)) {
                case 1:
                    // order(expr)
                    $this->bOrders[] = $orders[0];
                    break;
                case 2:
                    // order('field', 'sort')
                    $this->bOrders[] = sprintf('`%s` %s', $orders[0], strtoupper($orders[1]));
                    break;
            }
        }

        return $this;
    }
}
