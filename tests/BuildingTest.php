<?php
/**
 * SQL builder tests
 * User: moyo
 * Date: 2018/4/19
 * Time: 10:35 AM
 */

namespace Carno\Database\SQL\Tests;

use function Carno\Coroutine\co;
use Carno\Database\SQL\Builder;
use Carno\Database\SQL\Tests\DBS\Mocker;
use PHPUnit\Framework\TestCase;
use Closure;

class BuildingTest extends TestCase
{
    private $mocker = null;

    public function __construct(?string $name = null, array $data = [], string $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->mocker = new Mocker;
    }

    public function testNormalSQL()
    {
        $this->case(co(function (Builder $builder) {
            yield $builder->select('a', 'b', 'c')->where(['id' => 2])->get();
        }), 'SELECT `a`,`b`,`c` FROM `table` WHERE `id` = ?100 LIMIT 0,1', [100 => "2"]);

        $this->case(co(function (Builder $builder) {
            yield $builder->where('area', 'hz')->list();
        }), 'SELECT * FROM `table` WHERE `area` = ?100', [100 => "hz"]);

        $this->case(co(function (Builder $builder) {
            yield $builder->where('max(id) = 999 and min(test) > 2')->limit(100)->list();
        }), 'SELECT * FROM `table` WHERE max(id) = 999 and min(test) > 2 LIMIT 0,100');

        $this->case(co(function (Builder $builder) {
            yield $builder->select('some fields')->order(['id', 'desc'], ['id2', 'asc'])->limit(2000, 100)->list();
        }), 'SELECT some fields FROM `table` ORDER BY `id` DESC,`id2` ASC LIMIT 2000,100');

        $this->case(co(function (Builder $builder) {
            yield $builder->select('some fields')
                ->order(['id', 'desc'], ['id2', 'asc'])
                ->group(['a', 'desc'], ['b', 'asc'])
                ->group('c', 'd')
                ->group(['f WITH ROLLUP'])
                ->limit(2000, 100)
                ->list();
        }), 'SELECT some fields FROM `table` GROUP BY `a` DESC,`b` ASC,`c`,`d`,f WITH ROLLUP ORDER BY `id` DESC,`id2` ASC LIMIT 2000,100');

        $this->case(co(function (Builder $builder) {
            yield $builder->data('"raw data set"')->insert(['a' => 'b', 'b' => 'c', 'c' => [0, 1, 2]]);
        }), 'INSERT INTO `table` SET "raw data set",`a` = ?100,`b` = ?101,`c` = ?102', [
            100 => "b",
            101 => "c",
            102 => "[0,1,2]",
        ]);

        $this->case(co(function (Builder $builder) {
            yield $builder->where('key', 'info')->update(['aaa' => 'bbb']);
        }), 'UPDATE `table` SET `aaa` = ?101 WHERE `key` = ?100', [100 => "info", 101 => "bbb"]);

        $this->case(co(function (Builder $builder) {
            yield $builder->delete('some', 'conditions');
        }), 'DELETE FROM `table` WHERE `some` = ?100', [100 => "conditions"]);
    }

    public function testWhereSQL()
    {
        $this->case(co(function (Builder $builder) {
            yield $builder->where(['id' => 1], ['ib' => 2])->get();
        }), 'SELECT * FROM `table` WHERE `id` = ?100 AND `ib` = ?101 LIMIT 0,1', [
            100 => "1",
            101 => "2",
        ]);

        $this->case(co(function (Builder $builder) {
            yield $builder->where(['id1', 'in', [1, 2, 3]], ['xx', '>=', 9])->where('id2', 'in', [4, 5, 6])->get();
        }), 'SELECT * FROM `table` WHERE `id1` IN (1,2,3) AND `xx` >= ?100 AND `id2` IN (4,5,6) LIMIT 0,1', [
            100 => "9",
        ]);

        $this->case(co(function (Builder $builder) {
            yield $builder->where(['a', 1], ['b', '<>', 2], ['c' => 3], 'raw sql')->get();
        }), 'SELECT * FROM `table` WHERE `a` = ?100 AND `b` <> ?101 AND `c` = ?102 AND raw sql LIMIT 0,1', [
            100 => "1",
            101 => "2",
            102 => "3",
        ]);

        $this->case(co(function (Builder $builder) {
            yield $builder->where(['c' => 3], 'raw sql', ['b', '<>', 2], ['a', 1])->get();
        }), 'SELECT * FROM `table` WHERE `c` = ?100 AND raw sql AND `b` <> ?101 AND `a` = ?102 LIMIT 0,1', [
            100 => "3",
            101 => "2",
            102 => "1",
        ]);
    }

    public function testWhereAndOrSQL()
    {
        $this->case(co(function (Builder $builder) {
            yield $builder->where('c1')->and(function (Builder $builder) {
                $builder->where('c2');
                $builder->where('c3');
            })->or(function (Builder $builder) {
                $builder->where('c4');
                $builder->where('c5');
            })->get();
        }), 'SELECT * FROM `table` WHERE c1 AND (c2 AND c3) OR (c4 AND c5) LIMIT 0,1');

        $this->case(co(function (Builder $builder) {
            yield $builder->where('c1')->or(function (Builder $builder) {
                $builder->where('c2');
                $builder->and(function (Builder $builder) {
                    $builder->where('c3')->where('c4');
                });
                $builder->or(function (Builder $builder) {
                    $builder->where('c5');
                    $builder->where('c6');
                });
            })->get();
        }), 'SELECT * FROM `table` WHERE c1 OR (c2 AND (c3 AND c4) OR (c5 AND c6)) LIMIT 0,1');

        $this->case(co(function (Builder $builder) {
            yield $builder->where('c1')->or(function (Builder $builder) {
                $builder->where('c2');
                $builder->or(function (Builder $builder) {
                    $builder->where('c3');
                    $builder->or(function (Builder $builder) {
                        $builder->where('c4');
                        $builder->or(function (Builder $builder) {
                            $builder->and(function (Builder $builder) {
                                $builder->where('c6');
                                $builder->and(function (Builder $builder) {
                                    $builder->or(function (Builder $builder) {
                                        $builder->where('c7');
                                        $builder->where('c8');
                                    });
                                });
                            });
                        });
                    });
                });
            })->get();
        }), 'SELECT * FROM `table` WHERE c1 OR (c2 OR (c3 OR (c4 OR (1 AND (c6 AND (1 OR (c7 AND c8))))))) LIMIT 0,1');
    }

    public function testFunctionalSQL()
    {
        $this->case(co(function (Builder $builder) {
            yield $builder->exists(112233);
        }), 'SELECT COUNT(1) AS A_R_K FROM `table` WHERE `id` = ?100 LIMIT 0,1', [100 => "112233"]);

        $this->case(co(function (Builder $builder) {
            yield $builder->count('DISTINCT country');
        }), 'SELECT COUNT(DISTINCT country) AS A_R_K FROM `table` LIMIT 0,1');

        foreach (['sum', 'max', 'min', 'avg'] as $func) {
            $this->case(co(function (Builder $builder) use ($func) {
                yield $builder->$func('key');
            }), 'SELECT '.strtoupper($func).'(key) AS A_R_K FROM `table` LIMIT 0,1');
        }
    }

    public function testPaginationSQL()
    {
        $this->case(co(function (Builder $builder) {
            yield $builder->where('id', 112233)->limit(111, 222)->paged(32, 100);
        }), [
            'SELECT COUNT(1) AS A_R_K FROM `table` WHERE `id` = ?100 LIMIT 0,1',
            'SELECT * FROM `table` WHERE `id` = ?100 LIMIT 3100,100',
        ], [
            [100 => "112233"],
            [100 => "112233"],
        ]);

        $this->case(co(function (Builder $builder) {
            yield $builder->select('some fields')->order('id desc')->paged(2, 100);
        }), [
            'SELECT COUNT(1) AS A_R_K FROM `table` LIMIT 0,1',
            'SELECT some fields FROM `table` ORDER BY id desc LIMIT 100,100',
        ]);

        // TODO "group" should not valid in pagination
        $this->case(co(function (Builder $builder) {
            yield $builder->order('id desc')->group('app')->paged(3, 100);
        }), [
            'SELECT COUNT(1) AS A_R_K FROM `table` GROUP BY `app` LIMIT 0,1',
            'SELECT * FROM `table` GROUP BY `app` ORDER BY id desc LIMIT 200,100',
        ]);
    }

    private function case(Closure $closure, $sql, array $bind = [])
    {
        $this->mocker->clear();

        $closure($this->mocker->table('table'));

        if (is_array($sql)) {
            $sqls = $this->mocker->sqls();
            foreach ($sql as $i => $got) {
                $this->assertEquals($got, $sqls[$i], 'sql idx is '.$i);
            }
        } else {
            $this->assertEquals($sql, $this->mocker->sql());
        }

        if ($bind) {
            if (is_array($sql)) {
                $binds = $this->mocker->binds();
                foreach ($bind as $i => $dat) {
                    $this->assertArraySubset($dat, $binds[$i], true, 'bind idx is '.$i);
                }
            } else {
                $this->assertArraySubset($bind, $this->mocker->bind(), true);
            }
        }
    }
}
