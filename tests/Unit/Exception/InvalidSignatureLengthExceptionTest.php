<?php

use CardanoPhp\CIP8Verifier\Exception\InvalidSignatureLengthException;
use CardanoPhp\CIP8Verifier\Exception\CIP8VerificationException;

describe('InvalidSignatureLengthException', function () {
    test('extends CIP8VerificationException', function () {
        $exception = new InvalidSignatureLengthException(32, 64);

        expect($exception)->toBeInstanceOf(CIP8VerificationException::class);
    });

    test('creates correct error message with actual and expected lengths', function () {
        $actualLength = 32;
        $expectedLength = 64;
        $exception = new InvalidSignatureLengthException($actualLength, $expectedLength);

        expect($exception->getMessage())->toBe("Invalid signature length: {$actualLength} bytes, expected {$expectedLength}");
    });

    test('works with different length values', function () {
        $exception = new InvalidSignatureLengthException(0, SODIUM_CRYPTO_SIGN_BYTES);

        expect($exception->getMessage())->toBe('Invalid signature length: 0 bytes, expected ' . SODIUM_CRYPTO_SIGN_BYTES);
    });

    test('works with sodium constant', function () {
        $actualLength = 32;
        $expectedLength = SODIUM_CRYPTO_SIGN_BYTES;
        $exception = new InvalidSignatureLengthException($actualLength, $expectedLength);

        expect($exception->getMessage())->toBe("Invalid signature length: {$actualLength} bytes, expected {$expectedLength}");
    });
});