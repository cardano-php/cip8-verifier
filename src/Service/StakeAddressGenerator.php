<?php

namespace CardanoPhp\CIP8Verifier\Service;

use CardanoPhp\CIP8Verifier\Utility\Blake2bHasher;
use SodiumException;

class StakeAddressGenerator
{
    public function __construct(
        private Bech32Encoder $bech32Encoder
    ) {}

    /**
     * @throws SodiumException
     */
    public function generateStakeAddress(string $publicKey, int $networkMode): string
    {
        $stakeKey = $this->publicKeyToStakeKey($publicKey, $networkMode);
        $stakePrefix = $networkMode === 1 ? 'stake' : 'stake_test';

        return $this->bech32Encoder->stakeToBech32($stakeKey, $stakePrefix);
    }

    /**
     * @throws SodiumException
     */
    private function publicKeyToStakeKey(string $publicKey, int $networkMode): string
    {
        $publicKeyHash = Blake2bHasher::hash($publicKey, 28);
        return chr(0xe0 | $networkMode) . $publicKeyHash;
    }
}
