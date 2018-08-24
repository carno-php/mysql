<?php
/**
 * Where statements
 * User: moyo
 * Date: 22/12/2017
 * Time: 4:22 PM
 */

namespace Carno\Database\SQL\Builders;

use Carno\Database\SQL\Builder;
use Carno\Database\SQL\Exception\InvalidWhereStatementException;
use Closure;

trait Where
{
    /**
     * @var array
     */
    private $bWheres = [];

    /**
     * @var string
     */
    private $bWGrouped = null;

    /**
     * @var array
     */
    private $bWGroupSTA = [];

    /**
     * @var int
     */
    private $bWGDepth = 0;

    /**
     * @return string
     */
    protected function gWheres() : string
    {
        return ($sql = $this->gWStmt()) ? sprintf(' WHERE %s', $sql) : '';
    }

    /**
     * @return string
     */
    private function gWStmt() : string
    {
        $stmt = '';

        $stash = $this->bWGrouped[$this->bWGDepth] ?? null
            ? $this->bWGroupSTA[$this->bWGDepth]
            : $this->bWheres
        ;

        $pack = $cond = false;

        foreach ($stash as $part) {
            if (substr($part, 0, 1) === '#') {
                $pack = true;
                $stmt .= substr($part, 1);
            } else {
                $cond = true;
                $stmt .= $stmt ? sprintf(' AND %s', $part) : $part;
            }
        }

        ($pack && !$cond) && $stmt = sprintf('1%s', $stmt);

        return $stmt;
    }

    /**
     * @param array ...$conditions
     * @return Builder
     */
    public function where(...$conditions) : Builder
    {
        $expr = false;

        foreach ($conditions as $condition) {
            if (is_array($condition)) {
                // expr sta
                $expr = true;

                // where([expr], [expr])
                if (isset($condition[0])) {
                    $this->where(...$condition);
                    continue;
                }

                // where([k1 => v1, k2 => v2], [k3 => v3])
                foreach ($condition as $k => $v) {
                    $this->jWStmt($k, $v);
                }
            } elseif ($expr) {
                // -> where([expr])
                $this->jWStmt(...(is_array($condition) ? $condition : [$condition]));
            } else {
                // where(expr)
                $this->jWStmt(...$conditions);
                break;
            }
        }

        return $this;
    }

    /**
     * @param mixed $current
     * @param mixed ...$extras
     */
    private function jWStmt($current, ...$extras) : void
    {
        if (is_string($current)) {
            switch (count($extras)) {
                case 0:
                    // where('sql')
                    $this->addWSta($current);
                    break;
                case 1:
                    // where('key', 'val')
                    $this->addWSta($this->genWSQL($current, '=', $extras[0]));
                    break;
                case 2:
                    // where('key', 'exp', 'val')
                    $this->addWSta($this->genWSQL($current, ...$extras));
                    break;
                default:
                    throw new InvalidWhereStatementException;
            }
        } elseif (is_integer($current)) {
            // where(123) primary key
            $this->addWSta($this->genWSQL('id', '=', $current));
        } else {
            throw new InvalidWhereStatementException;
        }
    }

    /**
     * Grouping "AND"
     * @param Closure $builder
     * @return Builder
     */
    public function and(Closure $builder) : Builder
    {
        return $this->jWStack($builder, 'AND');
    }

    /**
     * Grouping "OR"
     * @param Closure $builder
     * @return Builder
     */
    public function or(Closure $builder) : Builder
    {
        return $this->jWStack($builder, 'OR');
    }

    /**
     * @param Closure $builder
     * @param string $group
     * @return Builder
     */
    private function jWStack(Closure $builder, string $group) : Builder
    {
        $this->bWGDepth ++;

        $this->bWGrouped[$this->bWGDepth] = $group;
        $this->bWGroupSTA[$this->bWGDepth] = [];

        $builder($this);

        $stage = $this->gWStmt();

        unset($this->bWGrouped[$this->bWGDepth]);
        unset($this->bWGroupSTA[$this->bWGDepth]);

        $this->bWGDepth --;

        $this->addWSta(sprintf('# %s (%s)', $group, $stage));

        return $this;
    }

    /**
     * @param string $sql
     */
    private function addWSta(string $sql) : void
    {
        if ($this->bWGrouped[$this->bWGDepth] ?? null) {
            $this->bWGroupSTA[$this->bWGDepth][] = $sql;
        } else {
            $this->bWheres[] = $sql;
        }
    }

    /**
     * @param string $key
     * @param string $expr
     * @param mixed $val
     * @return string
     */
    private function genWSQL(string $key, string $expr, $val) : string
    {
        $expr = strtoupper($expr);

        switch ($expr) {
            case 'IN':
                is_array($val) && $val = sprintf('(%s)', implode(',', array_map(function ($id) {
                    return (int) $id;
                }, $val)));
                break;
            default:
                $val = sprintf('?%d', $this->stash($val));
        }

        return sprintf('`%s` %s %s', $key, $expr, $val);
    }
}
