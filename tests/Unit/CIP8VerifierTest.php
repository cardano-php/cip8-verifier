<?php

use CardanoPhp\CIP8Verifier\CIP8Verifier;
use CardanoPhp\CIP8Verifier\DTO\VerificationRequest;
use CardanoPhp\CIP8Verifier\DTO\VerificationResult;
use CardanoPhp\CIP8Verifier\Service\PublicKeyExtractor;
use CardanoPhp\CIP8Verifier\Service\StakeAddressGenerator;
use CardanoPhp\CIP8Verifier\Service\CoseParser;
use CardanoPhp\CIP8Verifier\Service\SignatureVerifier;
use CardanoPhp\CIP8Verifier\Service\Bech32Encoder;
use CardanoPhp\CIP8Verifier\Exception\CIP8VerificationException;

describe('CIP8Verifier', function () {
    test('can be instantiated with dependencies', function () {
        $publicKeyExtractor = new PublicKeyExtractor();
        $bech32Encoder = new Bech32Encoder();
        $stakeAddressGenerator = new StakeAddressGenerator($bech32Encoder);
        $coseParser = new CoseParser();
        $signatureVerifier = new SignatureVerifier();
        
        $verifier = new CIP8Verifier(
            $publicKeyExtractor,
            $stakeAddressGenerator,
            $coseParser,
            $signatureVerifier
        );
        
        expect($verifier)->toBeInstanceOf(CIP8Verifier::class);
    });

    test('create static method returns configured instance', function () {
        $verifier = CIP8Verifier::create();
        
        expect($verifier)->toBeInstanceOf(CIP8Verifier::class);
    });

    test('verify method returns VerificationResult for valid data', function () {
        $verifier = CIP8Verifier::create();
        
        // Use valid demo data that we know works
        $request = new VerificationRequest(
            "84582aa201276761646472657373581de07a9647d2048870a0726f78621863e03797dc17b946473a35ded45f75a166686173686564f4582431633364353630312d386563632d343264662d623162302d3061323934643061346564355840d40e65ebb258bd48d04092f485b845a6c0c9b1728e896c8364e51e1b6d67cd2c36dc17ad52409671a8ac8e2376e3bf138869621d03c28841a50cd68bc34fa108",
            "a4010103272006215820eb59d52fbd257d3f8f8f51dd59b2013092763fc9cbc109d32d837920be5e62be",
            "31633364353630312d386563632d343264662d623162302d306132393464306134656435",
            "stake_test1upafv37jqjy8pgrjdauxyxrruqme0hqhh9ryww34mm297agc0f3vc",
            0
        );
        
        $result = $verifier->verify($request);
        
        expect($result)->toBeInstanceOf(VerificationResult::class);
    });

    test('verify method with demo data returns valid result', function () {
        $verifier = CIP8Verifier::create();
        
        $request = new VerificationRequest(
            "84582aa201276761646472657373581de07a9647d2048870a0726f78621863e03797dc17b946473a35ded45f75a166686173686564f4582431633364353630312d386563632d343264662d623162302d3061323934643061346564355840d40e65ebb258bd48d04092f485b845a6c0c9b1728e896c8364e51e1b6d67cd2c36dc17ad52409671a8ac8e2376e3bf138869621d03c28841a50cd68bc34fa108",
            "a4010103272006215820eb59d52fbd257d3f8f8f51dd59b2013092763fc9cbc109d32d837920be5e62be",
            "31633364353630312d386563632d343264662d623162302d306132393464306134656435",
            "stake_test1upafv37jqjy8pgrjdauxyxrruqme0hqhh9ryww34mm297agc0f3vc",
            0
        );
        
        $result = $verifier->verify($request);
        
        expect($result->isValid)->toBeTrue();
        expect($result->walletMatches)->toBeTrue();
        expect($result->payloadMatches)->toBeTrue();
        expect($result->signatureValidates)->toBeTrue();
    });

    test('verify method with invalid stake address returns false for wallet matches', function () {
        $verifier = CIP8Verifier::create();
        
        $request = new VerificationRequest(
            "84582aa201276761646472657373581de07a9647d2048870a0726f78621863e03797dc17b946473a35ded45f75a166686173686564f4582431633364353630312d386563632d343264662d623162302d3061323934643061346564355840d40e65ebb258bd48d04092f485b845a6c0c9b1728e896c8364e51e1b6d67cd2c36dc17ad52409671a8ac8e2376e3bf138869621d03c28841a50cd68bc34fa108",
            "a4010103272006215820eb59d52fbd257d3f8f8f51dd59b2013092763fc9cbc109d32d837920be5e62be",
            "31633364353630312d386563632d343264662d623162302d306132393464306134656435",
            "stake_test1wrong_address_that_doesnt_match",
            0
        );
        
        $result = $verifier->verify($request);
        
        expect($result->isValid)->toBeFalse();
        expect($result->walletMatches)->toBeFalse();
        expect($result->payloadMatches)->toBeTrue();
        expect($result->signatureValidates)->toBeTrue();
    });

    test('verify method with wrong network mode returns false for wallet matches', function () {
        $verifier = CIP8Verifier::create();
        
        $request = new VerificationRequest(
            "84582aa201276761646472657373581de07a9647d2048870a0726f78621863e03797dc17b946473a35ded45f75a166686173686564f4582431633364353630312d386563632d343264662d623162302d3061323934643061346564355840d40e65ebb258bd48d04092f485b845a6c0c9b1728e896c8364e51e1b6d67cd2c36dc17ad52409671a8ac8e2376e3bf138869621d03c28841a50cd68bc34fa108",
            "a4010103272006215820eb59d52fbd257d3f8f8f51dd59b2013092763fc9cbc109d32d837920be5e62be",
            "31633364353630312d386563632d343264662d623162302d306132393464306134656435",
            "stake_test1upafv37jqjy8pgrjdauxyxrruqme0hqhh9ryww34mm297agc0f3vc",
            1 // Wrong network mode (should be 0 for testnet)
        );
        
        $result = $verifier->verify($request);
        
        expect($result->isValid)->toBeFalse();
        expect($result->walletMatches)->toBeFalse();
        expect($result->payloadMatches)->toBeTrue();
        expect($result->signatureValidates)->toBeTrue();
    });

    test('verify method throws exception for invalid input', function () {
        $verifier = CIP8Verifier::create();
        
        $request = new VerificationRequest(
            'ff', // Valid hex but invalid CBOR
            'aa', // Valid hex but invalid signature key
            'bb', // Valid hex but invalid challenge
            'invalid_address',
            0
        );
        
        expect(fn() => $verifier->verify($request))->toThrow(Exception::class);
    });

});
