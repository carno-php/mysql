<?php
/**
 * Action observer
 * User: moyo
 * Date: 25/12/2017
 * Time: 5:56 PM
 */

namespace Carno\Database\SQL\Actions;

use Carno\Database\SQL\Builder;
use Closure;

trait Observer
{
    /**
     * @var Closure[]
     */
    private $actWatchers = [];

    /**
     * @var Closure[]
     */
    private $rsWatchers = [];

    /**
     * @param int $action
     * @param Closure ...$programs
     */
    protected function actWatching(int $action, Closure ...$programs) : void
    {
        $this->actWatchers[$action] = array_merge($this->actWatchers[$action] ?? [], $programs);
    }

    /**
     * @param int $action
     * @param Builder $instance
     */
    protected function actTrigger(int $action, Builder $instance) : void
    {
        foreach ($this->actWatchers[$action] ?? [] as $watcher) {
            $watcher($instance);
        }
    }

    /**
     * @param Closure ...$programs
     */
    protected function rsWatching(Closure ...$programs) : void
    {
        $this->rsWatchers = array_merge($this->rsWatchers, $programs);
    }

    /**
     * @param array $rows
     * @return array
     */
    protected function rsTrigger(array $rows)
    {
        foreach ($this->rsWatchers as $watcher) {
            $rows = yield $watcher($rows);
        }

        return $rows;
    }
}
