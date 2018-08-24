<?php
/**
 * Columns groups
 * User: moyo
 * Date: 2018/5/13
 * Time: 11:18 AM
 */

namespace Carno\Database\SQL\Builders;

use Carno\Database\SQL\Builder;

trait Group
{
    /**
     * @var array
     */
    private $bGroups = [];

    /**
     * @return string
     */
    protected function gGroups() : string
    {
        return $this->bGroups ? sprintf(' GROUP BY %s', implode(',', $this->bGroups)) : '';
    }

    /**
     * @param mixed ...$groups
     * @return Builder
     */
    public function group(...$groups) : Builder
    {
        foreach ($groups as $group) {
            if (is_array($group)) {
                switch (count($group)) {
                    case 1:
                        // group([expr])
                        $this->bGroups[] = $group[0];
                        break;
                    case 2:
                        // group([column, order])
                        $this->bGroups[] = sprintf('`%s` %s', $group[0], strtoupper($group[1]));
                        break;
                }
            } else {
                // group(column1, column2)
                $this->bGroups[] = sprintf('`%s`', $group);
            }
        }

        return $this;
    }
}
