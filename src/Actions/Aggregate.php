<?php
/**
 * Aggregate functions
 * User: moyo
 * Date: 2018/4/19
 * Time: 11:13 AM
 */

namespace Carno\Database\SQL\Actions;

trait Aggregate
{
    /**
     * @var string
     */
    private $ark = 'A_R_K';

    /**
     * @param mixed ...$conditions
     * @return bool
     */
    public function exists(...$conditions)
    {
        $conditions && $this->where(...$conditions);

        return (yield $this->count()) > 0 ? true : false;
    }

    /**
     * @param string $expr
     * @return int
     */
    public function count(string $expr = '1')
    {
        return (int) yield $this->calc(sprintf('COUNT(%s)', $expr));
    }

    /**
     * @param string $expr
     * @return float
     */
    public function sum(string $expr)
    {
        return (float) yield $this->calc(sprintf('SUM(%s)', $expr));
    }

    /**
     * @param string $expr
     * @return float
     */
    public function max(string $expr)
    {
        return (float) yield $this->calc(sprintf('MAX(%s)', $expr));
    }

    /**
     * @param string $expr
     * @return float
     */
    public function min(string $expr)
    {
        return (float) yield $this->calc(sprintf('MIN(%s)', $expr));
    }

    /**
     * @param string $expr
     * @return float
     */
    public function avg(string $expr)
    {
        return (float) yield $this->calc(sprintf('AVG(%s)', $expr));
    }

    /**
     * @param string $expr
     * @return float
     */
    private function calc(string $expr)
    {
        $stmt = $this->backup('select', 'orders');

        $this->select(sprintf('%s AS %s', $expr, $this->ark));

        $result = (yield $this->get())[$this->ark] ?? 0;

        $this->restore($stmt);

        return $result;
    }

    /**
     * @param string ...$builds
     * @return array
     */
    private function backup(string ...$builds) : array
    {
        $saved = [];

        foreach ($builds as $build) {
            if (property_exists($this, $pt = sprintf('b%s', ucfirst($build)))) {
                $saved[$build] = $this->$pt;
                $this->$pt = null;
            }
        }

        return $saved;
    }

    /**
     * @param array $previous
     */
    private function restore(array $previous) : void
    {
        foreach ($previous as $build => $saved) {
            if (property_exists($this, $pt = sprintf('b%s', ucfirst($build)))) {
                $this->$pt = $saved;
            }
        }
    }
}
