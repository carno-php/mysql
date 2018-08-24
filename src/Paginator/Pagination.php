<?php
/**
 * Pagination items
 * User: moyo
 * Date: 2018/4/19
 * Time: 2:41 PM
 */

namespace Carno\Database\SQL\Paginator;

final class Pagination
{
    /**
     * @var int
     */
    private $total = 0;

    /**
     * @var int
     */
    private $size = 0;

    /**
     * @var int
     */
    private $prev = 1;

    /**
     * @var int
     */
    private $page = 1;

    /**
     * @var int
     */
    private $next = 1;

    /**
     * @var int
     */
    private $last = 1;

    /**
     * @var array
     */
    private $items = [];

    /**
     * Pagination constructor.
     * @param int $total
     * @param int $size
     * @param int $prev
     * @param int $page
     * @param int $next
     * @param int $last
     * @param array $items
     */
    public function __construct(int $total, int $size, int $prev, int $page, int $next, int $last, array $items)
    {
        $this->total = $total;
        $this->size = $size;
        $this->prev = $prev;
        $this->page = $page;
        $this->next = $next;
        $this->last = $last;
        $this->items = $items;
    }

    /**
     * @return int
     */
    public function total() : int
    {
        return $this->total;
    }

    /**
     * @return int
     */
    public function size() : int
    {
        return $this->size;
    }

    /**
     * @return int
     */
    public function prev() : int
    {
        return $this->prev;
    }

    /**
     * @return int
     */
    public function page() : int
    {
        return $this->page;
    }

    /**
     * @return int
     */
    public function next() : int
    {
        return $this->next;
    }

    /**
     * @return int
     */
    public function last() : int
    {
        return $this->last;
    }

    /**
     * @return array
     */
    public function items() : array
    {
        return $this->items;
    }

    /**
     * @param object $target
     * @return mixed
     */
    public function export(object $target) : object
    {
        foreach (['total', 'size', 'prev', 'page', 'next', 'last'] as $key) {
            if (method_exists($target, $func = sprintf('set%s', ucfirst($key)))) {
                $target->$func($this->$key);
            }
        }

        return $target;
    }
}
