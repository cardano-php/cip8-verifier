<?php

namespace CardanoPhp\CIP8Verifier\Service;

use CardanoPhp\CIP8Verifier\DTO\CoseSign1;
use CardanoPhp\CIP8Verifier\Utility\CborHelper;

class CoseParser
{
    public function parseCoseSign1(string $cborData): CoseSign1
    {
        $decoded = CborHelper::decode($cborData);

        $protected = $decoded->get(0);
        $unprotected = $decoded->get(1);
        $payload = $decoded->get(2);
        $signature = $decoded->get(3);

        if (method_exists($protected, 'getValue')) {
            $protected = $protected->getValue();
        }
        if (method_exists($payload, 'getValue')) {
            $payload = $payload->getValue();
        }
        if (method_exists($signature, 'getValue')) {
            $signature = $signature->getValue();
        }

        return new CoseSign1($protected, $unprotected, $payload, $signature);
    }
}
