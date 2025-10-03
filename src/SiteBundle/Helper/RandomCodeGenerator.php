<?php

namespace SiteBundle\Helper;

/**
 * Class RandomGenerator
 */
class RandomCodeGenerator
{
    /**
     * @param int $length
     *
     * @return string
     */
    public function random($length = 5)
    {
        $pool = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';

        $cryptoRandSecure = function ($min, $max) {
            $range = $max - $min;
            $log    = log($range, 2);
            $bytes  = (int) ( $log / 8 ) + 1; // length in bytes
            $bits   = (int) $log + 1; // length in bits
            $filter = (int) ( 1 << $bits ) - 1; // set all lower bits to 1
            do {
                $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
                $rnd = $rnd & $filter; // discard irrelevant bits
            } while ($rnd >= $range);

            return $min + $rnd;
        };

        $token = "";
        $max = strlen($pool);
        for ($i = 0; $i < $length; $i++) {
            $token .= $pool[$cryptoRandSecure(0, $max)];
        }

        return $token;
    }
}
