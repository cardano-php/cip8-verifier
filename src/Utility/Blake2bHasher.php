<?php

namespace CardanoPhp\CIP8Verifier\Utility;

use SodiumException;

class Blake2bHasher
{
    /**
     * @throws SodiumException
     */
    public static function hash(string $data, int $length = 32): string
    {
        return sodium_crypto_generichash($data, '', $length);
    }
}
