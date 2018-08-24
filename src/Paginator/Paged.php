<?php
/**
 * Paged builder
 * User: moyo
 * Date: 2018/4/19
 * Time: 2:34 PM
 */

namespace Carno\Database\SQL\Paginator;

trait Paged
{
    /**
     * default page size
     * @var int
     */
    private $pSize = 20;

    /**
     * @param mixed ...$input
     * @return Pagination
     */
    public function paged(...$input)
    {
        list($page, $size) =
            count($input) === 1 && is_object($input[0])
                ? $this->import($input[0])
                : [$input[0] ?? 1, $input[1] ?? $this->pSize]
        ;

        $total = yield $this->count();

        $last = ceil($total / $size);

        $next = min($page + 1, $last);
        $prev = max($page - 1, 1);

        $this->limit(($page - 1) * $size, $size);

        return new Pagination($total, $size, $prev, $page, $next, $last, yield $this->list());
    }

    /**
     * @param object $source
     * @return array
     */
    private function import(object $source) : array
    {
        $page = $size = null;

        if (method_exists($source, 'getPage')) {
            $page = (int)$source->getPage();
        }

        if (method_exists($source, 'getSize')) {
            $size = (int)$source->getSize();
        }

        return [$page ?: 1, $size ?: $this->pSize];
    }
}
