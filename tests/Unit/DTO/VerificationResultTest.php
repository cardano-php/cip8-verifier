<?php

use CardanoPhp\CIP8Verifier\DTO\VerificationResult;

describe('VerificationResult', function () {
    test('can be instantiated with all parameters', function () {
        $result = new VerificationResult(
            true,
            true,
            true,
            true,
            null
        );

        expect($result->isValid)->toBeTrue();
        expect($result->walletMatches)->toBeTrue();
        expect($result->payloadMatches)->toBeTrue();
        expect($result->signatureValidates)->toBeTrue();
        expect($result->error)->toBeNull();
    });

    test('can be instantiated with error message', function () {
        $result = new VerificationResult(
            false,
            false,
            false,
            false,
            'Test error message'
        );

        expect($result->isValid)->toBeFalse();
        expect($result->walletMatches)->toBeFalse();
        expect($result->payloadMatches)->toBeFalse();
        expect($result->signatureValidates)->toBeFalse();
        expect($result->error)->toBe('Test error message');
    });

    test('toArray returns correct array structure', function () {
        $result = new VerificationResult(
            true,
            true,
            false,
            true,
            null
        );

        $array = $result->toArray();

        expect($array)->toBe([
            'isValid' => true,
            'walletMatches' => true,
            'payloadMatches' => false,
            'signatureValidates' => true,
            'error' => null
        ]);
    });

    test('toArray returns correct array structure with error', function () {
        $result = new VerificationResult(
            false,
            false,
            false,
            false,
            'Test error'
        );

        $array = $result->toArray();

        expect($array)->toBe([
            'isValid' => false,
            'walletMatches' => false,
            'payloadMatches' => false,
            'signatureValidates' => false,
            'error' => 'Test error'
        ]);
    });

    test('createValid returns valid result when all checks pass', function () {
        $result = VerificationResult::createValid(true, true, true);

        expect($result->isValid)->toBeTrue();
        expect($result->walletMatches)->toBeTrue();
        expect($result->payloadMatches)->toBeTrue();
        expect($result->signatureValidates)->toBeTrue();
        expect($result->error)->toBeNull();
    });

    test('createValid returns invalid result when any check fails', function () {
        $result = VerificationResult::createValid(true, false, true);

        expect($result->isValid)->toBeFalse();
        expect($result->walletMatches)->toBeTrue();
        expect($result->payloadMatches)->toBeFalse();
        expect($result->signatureValidates)->toBeTrue();
        expect($result->error)->toBeNull();
    });

    test('createValid returns invalid result when wallet check fails', function () {
        $result = VerificationResult::createValid(false, true, true);

        expect($result->isValid)->toBeFalse();
        expect($result->walletMatches)->toBeFalse();
        expect($result->payloadMatches)->toBeTrue();
        expect($result->signatureValidates)->toBeTrue();
        expect($result->error)->toBeNull();
    });

    test('createValid returns invalid result when signature check fails', function () {
        $result = VerificationResult::createValid(true, true, false);

        expect($result->isValid)->toBeFalse();
        expect($result->walletMatches)->toBeTrue();
        expect($result->payloadMatches)->toBeTrue();
        expect($result->signatureValidates)->toBeFalse();
        expect($result->error)->toBeNull();
    });

    test('createInvalid returns invalid result with error message', function () {
        $result = VerificationResult::createInvalid('Test error message');

        expect($result->isValid)->toBeFalse();
        expect($result->walletMatches)->toBeFalse();
        expect($result->payloadMatches)->toBeFalse();
        expect($result->signatureValidates)->toBeFalse();
        expect($result->error)->toBe('Test error message');
    });
});