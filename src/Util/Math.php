<?php

namespace Aternos\Renderchest\Util;

class Math
{
    /**
     * Greatest common divisor of two numbers
     *
     * @param int $a
     * @param int $b
     * @return int
     */
    public static function gcd(int $a, int $b): int
    {
        if ($b === 0) {
            return $a;
        }

        return static::gcd($b, $a % $b);
    }

    /**
     * Least common multiple of an array of numbers
     *
     * @param int[] $input
     * @return int
     */
    public static function lcm(array $input): int
    {
        $count = count($input);
        if ($count === 0) {
            return 0;
        }

        $result = $input[0];

        for ($i = 1; $i < $count; $i++) {
            $result = intdiv($input[$i] * $result, static::gcd($input[$i], $result));
        }

        return $result;
    }
}
