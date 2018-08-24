<?php
/**
 * Data stash
 * User: moyo
 * Date: 25/12/2017
 * Time: 2:54 PM
 */

namespace Carno\Database\SQL\Builders;

use Carno\Database\SQL\Builder;

trait Data
{
    /**
     * @var array
     */
    private $bDataE = [];

    /**
     * @var array
     */
    private $bDataM = [];

    /**
     * @return string
     */
    protected function gData() : string
    {
        $dMaps = [];
        array_walk($this->bDataM, function ($v, $k) use (&$dMaps) {
            $dMaps[] = sprintf('`%s` = ?%d', $k, $this->stash($v));
        });

        return sprintf('SET %s', implode(',', array_merge($this->bDataE, $dMaps)));
    }

    /**
     * @param array ...$maps
     * @return Builder
     */
    public function data(...$maps) : Builder
    {
        foreach ($maps as $map) {
            if (is_string($map)) {
                // data(expr2)
                $this->bDataE[] = $map;
            } else {
                // data([key => val])
                foreach ($map as $mk => $mv) {
                    $this->bDataM[$mk] =
                        is_scalar($mv)
                            ? $mv
                            : json_encode($mv, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
                    ;
                }
            }
        }

        return $this;
    }
}
