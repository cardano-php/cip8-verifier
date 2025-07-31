<?php

use CardanoPhp\CIP8Verifier\DTO\VerificationResult;

describe('VerificationResult', function () {
    test('can be instantiated with all parameters', function () {
        $result = new VerificationResult(
            true,
            true,
            true,
            true
        );

        expect($result->isValid)->toBeTrue();
        expect($result->stakeAddressMatches)->toBeTrue();
        expect($result->challengeMatches)->toBeTrue();
        expect($result->signatureValidates)->toBeTrue();

    });



    test('toArray returns correct array structure', function () {
        $result = new VerificationResult(
            true,
            true,
            false,
            true
        );

        $array = $result->toArray();

        expect($array)->toBe([
            'isValid' => true,
            'stakeAddressMatches' => true,
            'challengeMatches' => false,
            'signatureValidates' => true
        ]);
    });



    test('createValid returns valid result when all checks pass', function () {
        $result = VerificationResult::createValid(true, true, true);

        expect($result->isValid)->toBeTrue();
        expect($result->stakeAddressMatches)->toBeTrue();
        expect($result->challengeMatches)->toBeTrue();
        expect($result->signatureValidates)->toBeTrue();

    });

    test('createValid returns invalid result when any check fails', function () {
        $result = VerificationResult::createValid(true, false, true);

        expect($result->isValid)->toBeFalse();
        expect($result->stakeAddressMatches)->toBeTrue();
        expect($result->challengeMatches)->toBeFalse();
        expect($result->signatureValidates)->toBeTrue();

    });

    test('createValid returns invalid result when wallet check fails', function () {
        $result = VerificationResult::createValid(false, true, true);

        expect($result->isValid)->toBeFalse();
        expect($result->stakeAddressMatches)->toBeFalse();
        expect($result->challengeMatches)->toBeTrue();
        expect($result->signatureValidates)->toBeTrue();

    });

    test('createValid returns invalid result when signature check fails', function () {
        $result = VerificationResult::createValid(true, true, false);

        expect($result->isValid)->toBeFalse();
        expect($result->stakeAddressMatches)->toBeTrue();
        expect($result->challengeMatches)->toBeTrue();
        expect($result->signatureValidates)->toBeFalse();

    });


});
