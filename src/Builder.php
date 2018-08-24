<?php
/**
 * SQL Builder
 * User: moyo
 * Date: 22/12/2017
 * Time: 4:16 PM
 */

namespace Carno\Database\SQL;

use Carno\Database\Contracts\Executable;
use Carno\Database\SQL\Actions\Aggregate;
use Carno\Database\SQL\Actions\Delete;
use Carno\Database\SQL\Actions\Getter;
use Carno\Database\SQL\Actions\Insert;
use Carno\Database\SQL\Actions\Observer;
use Carno\Database\SQL\Actions\Update;
use Carno\Database\SQL\Builders\Data;
use Carno\Database\SQL\Builders\Group;
use Carno\Database\SQL\Builders\Limit;
use Carno\Database\SQL\Builders\Order;
use Carno\Database\SQL\Builders\Params;
use Carno\Database\SQL\Builders\Select;
use Carno\Database\SQL\Builders\Where;
use Carno\Database\SQL\Exception\UnknownActionException;
use Carno\Database\SQL\Merging\Related;
use Carno\Database\SQL\Paginator\Paged;

class Builder
{
    use Observer, Params;
    use Select, Data, Where, Group, Order, Limit;
    use Insert, Update, Delete;
    use Getter, Aggregate;
    use Related, Paged;

    /**
     * @var string
     */
    protected $table = 'table';

    /**
     * @var Executable
     */
    protected $executor = null;

    /**
     * Builder constructor.
     * @param string $table
     * @param Executable $executor
     * @param array $observers
     */
    public function __construct(string $table, Executable $executor, array $observers = [])
    {
        $this->table = $table;
        $this->executor = $executor;

        foreach ($observers as $action => $programs) {
            $this->actWatching($action, ...$programs);
        }
    }

    /**
     * @param string $table
     * @return Builder
     */
    protected function new(string $table = null) : Builder
    {
        return new static($table ?? $this->table, $this->executor);
    }

    /**
     * @param string $sql
     * @return mixed
     */
    protected function exec(string $sql)
    {
        return $this->executor->execute($sql, $this->params());
    }

    /**
     * @param int $action
     * @return string
     */
    protected function sql(int $action) : string
    {
        $sql = null;

        switch ($action) {
            case Action::INSERT:
                $sql = sprintf(
                    'INSERT INTO `%s` %s',
                    $this->table,
                    $this->gData()
                );
                break;
            case Action::SELECT:
                $sql = sprintf(
                    'SELECT %s FROM `%s`%s%s%s%s',
                    $this->gSelect(),
                    $this->table,
                    $this->gWheres(),
                    $this->gGroups(),
                    $this->gOrders(),
                    $this->gLimit()
                );
                break;
            case Action::UPDATE:
                $sql = sprintf(
                    'UPDATE `%s` %s%s%s%s',
                    $this->table,
                    $this->gData(),
                    $this->gWheres(),
                    $this->gOrders(),
                    $this->gLimit()
                );
                break;
            case Action::DELETE:
                $sql = sprintf(
                    'DELETE FROM `%s`%s%s%s',
                    $this->table,
                    $this->gWheres(),
                    $this->gOrders(),
                    $this->gLimit()
                );
                break;
        }

        if ($sql) {
            return rtrim($sql);
        }

        throw new UnknownActionException;
    }
}
