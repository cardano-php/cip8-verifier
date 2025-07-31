<?php

namespace CardanoPhp\CIP8Verifier\Service;

use CardanoPhp\CIP8Verifier\Utility\CborHelper;

class PublicKeyExtractor
{
    public function extractFromSignatureKey(string $signatureKey): string
    {
        $decoded = CborHelper::decode(hex2bin($signatureKey));

        $publicKeyBytes = $decoded->get(-2);

        if (method_exists($publicKeyBytes, 'getValue')) {
            $publicKeyBytes = $publicKeyBytes->getValue();
        }

        return $publicKeyBytes;
    }
}
