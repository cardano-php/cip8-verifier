<?php

namespace CardanoPhp\CIP8Verifier\Service;

use CardanoPhp\CIP8Verifier\DTO\CoseSign1;
use CardanoPhp\CIP8Verifier\Exception\InvalidPublicKeyLengthException;
use CardanoPhp\CIP8Verifier\Exception\InvalidSignatureLengthException;
use CardanoPhp\CIP8Verifier\Utility\Blake2bHasher;
use CardanoPhp\CIP8Verifier\Utility\CborHelper;
use SodiumException;

class SignatureVerifier
{
    /**
     * @throws InvalidSignatureLengthException
     * @throws InvalidPublicKeyLengthException
     * @throws SodiumException
     */
    public function verifySignature(CoseSign1 $coseData, string $publicKey): bool
    {
        $this->validateSignatureLength($coseData->signature);
        $this->validatePublicKeyLength($publicKey);

        $sigStructure = CborHelper::createSigStructure($coseData->protected, $coseData->payload);

        return sodium_crypto_sign_verify_detached(
            $coseData->signature,
            $sigStructure,
            $publicKey
        );
    }

    /**
     * @throws SodiumException
     */
    public function verifyPayload(CoseSign1 $coseData, string $challengeHex): bool
    {
        $signedPayloadHex = bin2hex($coseData->payload);

                return $signedPayloadHex === $challengeHex ||
            $signedPayloadHex === bin2hex(Blake2bHasher::hash(hex2bin($challengeHex), 28));
    }

    /**
     * @throws InvalidSignatureLengthException
     */
    private function validateSignatureLength(string $signature): void
    {
        if (strlen($signature) !== SODIUM_CRYPTO_SIGN_BYTES) {
            throw new InvalidSignatureLengthException(strlen($signature), SODIUM_CRYPTO_SIGN_BYTES);
        }
    }

    /**
     * @throws InvalidPublicKeyLengthException
     */
    private function validatePublicKeyLength(string $publicKey): void
    {
        if (strlen($publicKey) !== SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES) {
            throw new InvalidPublicKeyLengthException(strlen($publicKey), SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES);
        }
    }
}
