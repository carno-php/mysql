<?php
/**
 * Params stash
 * User: moyo
 * Date: 26/12/2017
 * Time: 4:43 PM
 */

namespace Carno\Database\SQL\Builders;

trait Params
{
    /**
     * @var int
     */
    private $pid = 100;

    /**
     * @var array
     */
    private $vars = [];

    /**
     * @return array
     */
    protected function params() : array
    {
        return $this->vars;
    }

    /**
     * @param string $v
     * @return int
     */
    protected function stash(string $v) : int
    {
        $this->vars[$this->pid] = $v;
        return $this->pid ++;
    }
}
