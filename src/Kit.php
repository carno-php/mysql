<?php
/**
 * Database/SQL kit
 * User: moyo
 * Date: 22/12/2017
 * Time: 3:37 PM
 */

namespace Carno\Database\SQL;

use Carno\Database\Contracts\Executable;

trait Kit
{
    /**
     * available features
     * @var array
     */
    private $features = [
        'timestamps',
    ];

    /**
     * loaded chips
     * @var array
     */
    private $chips = [];

    /**
     * @param string $name
     * @param Executable $executable
     * @return Builder
     */
    final public function table(string $name, Executable $executable = null) : Builder
    {
        return new Builder($name, $executable ?: $this, $this->fcInjectors());
    }

    /**
     * @return array
     */
    final private function fcInjectors() : array
    {
        if ($this->chips) {
            return $this->chips;
        }

        foreach ($this->features as $feature) {
            if (method_exists($this, $chip = sprintf('%sChip', $feature))) {
                $observers = $this->$chip();
                foreach ($observers as $observer) {
                    list($action, $program) = $observer;
                    $this->chips[$action][] = $program;
                }
            }
        }

        return $this->chips;
    }
}
