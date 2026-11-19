<?php

if (! function_exists('czk')) {
    function czk(int|float|null $v): string
    {
        return number_format((int) $v, 0, ',', ' ').' Kč';
    }
}

if (! function_exists('plural')) {
    /** Czech plural: plural(3, 'upgrade', 'upgrady', 'upgradů') */
    function plural(int $n, string $one, string $few, string $many): string
    {
        return $n === 1 ? $one : ($n >= 2 && $n <= 4 ? $few : $many);
    }
}
