<?php
/**
 * DBS mocker
 * User: moyo
 * Date: 2018/4/19
 * Time: 10:38 AM
 */

namespace Carno\Database\SQL\Tests\DBS;

use Carno\Database\Contracts\Executable;
use Carno\Database\Results\Created;
use Carno\Database\Results\Selected;
use Carno\Database\Results\Updated;
use Carno\Database\SQL\Kit;
use Carno\Promise\Promised;

class Mocker implements Executable
{
    use Kit;

    /**
     * @var string
     */
    private $sql = '';

    /**
     * @var string[]
     */
    private $sqls = [];

    /**
     * @var array
     */
    private $bind = [];

    /**
     * @var array
     */
    private $binds = [];

    /**
     * @return string
     */
    public function sql() : string
    {
        return $this->sql;
    }

    /**
     * @return array
     */
    public function sqls() : array
    {
        return $this->sqls;
    }

    /**
     * @return array
     */
    public function bind() : array
    {
        return $this->bind;
    }

    /**
     * @return array
     */
    public function binds() : array
    {
        return $this->binds;
    }

    /**
     */
    public function clear() : void
    {
        $this->sql = '';
        $this->sqls = [];
        $this->bind = [];
        $this->binds = [];
    }

    /**
     * @param string $sql
     * @param array $bind
     * @return Promised|Created|Updated|Selected
     * @throws \Exception
     */
    public function execute(string $sql, array $bind = [])
    {
        $this->sql = $sql;
        $this->sqls[] = $sql;
        $this->bind = $bind;
        $this->binds[] = $bind;

        switch (substr($sql, 0, 6)) {
            case 'INSERT':
                return new Created(1);
                break;
            case 'SELECT':
                return new Selected([]);
                break;
            case 'UPDATE':
                return new Updated(1);
                break;
            case 'DELETE':
                return new Updated(1);
                break;
            default:
                throw new \Exception('Unknown SQL');
        }
    }

    /**
     * @param string $data
     * @return string
     */
    public function escape(string $data) : string
    {
        return sprintf('"%s"', $data);
    }
}
