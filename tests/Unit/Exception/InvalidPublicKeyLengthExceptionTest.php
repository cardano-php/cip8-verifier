<?php

use CardanoPhp\CIP8Verifier\Exception\InvalidPublicKeyLengthException;
use CardanoPhp\CIP8Verifier\Exception\CIP8VerificationException;

describe('InvalidPublicKeyLengthException', function () {
    test('extends CIP8VerificationException', function () {
        $exception = new InvalidPublicKeyLengthException(10, 32);

        expect($exception)->toBeInstanceOf(CIP8VerificationException::class);
    });

    test('creates correct error message with actual and expected lengths', function () {
        $actualLength = 10;
        $expectedLength = 32;
        $exception = new InvalidPublicKeyLengthException($actualLength, $expectedLength);

        expect($exception->getMessage())->toBe("Invalid public key length: {$actualLength} bytes, expected {$expectedLength}");
    });

    test('works with different length values', function () {
        $exception = new InvalidPublicKeyLengthException(0, SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES);

        expect($exception->getMessage())->toBe('Invalid public key length: 0 bytes, expected ' . SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES);
    });

    test('works with sodium constant', function () {
        $actualLength = 16;
        $expectedLength = SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES;
        $exception = new InvalidPublicKeyLengthException($actualLength, $expectedLength);

        expect($exception->getMessage())->toBe("Invalid public key length: {$actualLength} bytes, expected {$expectedLength}");
    });
});