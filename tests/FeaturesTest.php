<?php
/**
 * Features test
 * User: moyo
 * Date: 2018/8/24
 * Time: 12:08 PM
 */

namespace Carno\Database\SQL\Tests;

use function Carno\Coroutine\co;
use Carno\Database\SQL\Builder;
use Carno\Database\SQL\Tests\DBS\Mocker2;
use PHPUnit\Framework\TestCase;
use Closure;

class FeaturesTest extends TestCase
{
    private $mocker = null;

    public function __construct(?string $name = null, array $data = [], string $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->mocker = new Mocker2;
    }

    public function testTimestamps()
    {
        // ts insert
        $this->case(co(function (Builder $builder) {
            yield $builder->insert(['aaa' => 'bbb']);
        }), 'INSERT INTO `table` SET `createdAt` = ?100,`updatedAt` = ?101,`aaa` = ?102', [
            100 => (string)time(),
            101 => (string)time(),
            102 => "bbb",
        ]);

        // ts update
        $this->case(co(function (Builder $builder) {
            yield $builder->where('key', 'info')->update(['aaa' => 'bbb']);
        }), 'UPDATE `table` SET `updatedAt` = ?101,`aaa` = ?102 WHERE `key` = ?100', [
            100 => "info",
            101 => (string)time(),
            102 => "bbb"
        ]);
    }

    private function case(Closure $closure, string $sql, array $bind = [])
    {
        $closure($this->mocker->table('table'));

        $this->assertEquals($sql, $this->mocker->sql());

        $bind && $this->assertArraySubset($bind, $this->mocker->bind(), true);
    }
}
