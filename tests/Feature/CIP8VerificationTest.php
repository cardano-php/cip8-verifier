<?php

use CardanoPhp\CIP8Verifier\CIP8Verifier;
use CardanoPhp\CIP8Verifier\DTO\VerificationRequest;
use CardanoPhp\CIP8Verifier\Exception\CIP8VerificationException;

describe('CIP8 Verification Feature', function () {
    test('complete end-to-end verification with demo data succeeds', function () {
        // This test replicates the exact demo.php scenario
        $signatureCbor = "84582aa201276761646472657373581de07a9647d2048870a0726f78621863e03797dc17b946473a35ded45f75a166686173686564f4582431633364353630312d386563632d343264662d623162302d3061323934643061346564355840d40e65ebb258bd48d04092f485b845a6c0c9b1728e896c8364e51e1b6d67cd2c36dc17ad52409671a8ac8e2376e3bf138869621d03c28841a50cd68bc34fa108";
        $signatureKey = "a4010103272006215820eb59d52fbd257d3f8f8f51dd59b2013092763fc9cbc109d32d837920be5e62be";
        $walletAuthChallengeHex = "31633364353630312d386563632d343264662d623162302d306132393464306134656435";
        $stakeKeyAddress = "stake_test1upafv37jqjy8pgrjdauxyxrruqme0hqhh9ryww34mm297agc0f3vc";
        $networkMode = 0;

        $verifier = CIP8Verifier::create();
        $request = new VerificationRequest(
            $signatureCbor,
            $signatureKey,
            $walletAuthChallengeHex,
            $stakeKeyAddress,
            $networkMode,
        );
        $result = $verifier->verify($request);

        // Verify all the expected results match demo.php output
        expect($result->isValid)->toBeTrue();
        expect($result->walletMatches)->toBeTrue();
        expect($result->payloadMatches)->toBeTrue();
        expect($result->signatureValidates)->toBeTrue();


        // Verify toArray() output matches expected structure
        $arrayResult = $result->toArray();
        expect($arrayResult)->toBe([
            'isValid' => true,
            'walletMatches' => true,
            'payloadMatches' => true,
            'signatureValidates' => true
        ]);
    });

    test('end-to-end verification with static method succeeds', function () {
        $event = [
            'signatureCbor' => "84582aa201276761646472657373581de07a9647d2048870a0726f78621863e03797dc17b946473a35ded45f75a166686173686564f4582431633364353630312d386563632d343264662d623162302d3061323934643061346564355840d40e65ebb258bd48d04092f485b845a6c0c9b1728e896c8364e51e1b6d67cd2c36dc17ad52409671a8ac8e2376e3bf138869621d03c28841a50cd68bc34fa108",
            'signatureKey' => "a4010103272006215820eb59d52fbd257d3f8f8f51dd59b2013092763fc9cbc109d32d837920be5e62be",
            'walletAuthChallengeHex' => "31633364353630312d386563632d343264662d623162302d306132393464306134656435",
            'stakeKeyAddress' => "stake_test1upafv37jqjy8pgrjdauxyxrruqme0hqhh9ryww34mm297agc0f3vc",
            'networkMode' => 0
        ];

        $request = VerificationRequest::fromArray($event);
        $result = CIP8Verifier::create()->verify($request);

        expect($result->isValid)->toBeTrue();
        expect($result->walletMatches)->toBeTrue();
        expect($result->payloadMatches)->toBeTrue();
        expect($result->signatureValidates)->toBeTrue();

    });

    test('verification fails when wallet address does not match', function () {
        $verifier = CIP8Verifier::create();
        $request = new VerificationRequest(
            "84582aa201276761646472657373581de07a9647d2048870a0726f78621863e03797dc17b946473a35ded45f75a166686173686564f4582431633364353630312d386563632d343264662d623162302d3061323934643061346564355840d40e65ebb258bd48d04092f485b845a6c0c9b1728e896c8364e51e1b6d67cd2c36dc17ad52409671a8ac8e2376e3bf138869621d03c28841a50cd68bc34fa108",
            "a4010103272006215820eb59d52fbd257d3f8f8f51dd59b2013092763fc9cbc109d32d837920be5e62be",
            "31633364353630312d386563632d343264662d623162302d306132393464306134656435",
            "stake_test1wrong_address_here", // Wrong stake address
            0
        );

        $result = $verifier->verify($request);

        expect($result->isValid)->toBeFalse();
        expect($result->walletMatches)->toBeFalse();
        expect($result->payloadMatches)->toBeTrue(); // Payload should still match
        expect($result->signatureValidates)->toBeTrue(); // Signature should still be valid

    });

    test('verification fails when network mode is incorrect', function () {
        $verifier = CIP8Verifier::create();
        $request = new VerificationRequest(
            "84582aa201276761646472657373581de07a9647d2048870a0726f78621863e03797dc17b946473a35ded45f75a166686173686564f4582431633364353630312d386563632d343264662d623162302d3061323934643061346564355840d40e65ebb258bd48d04092f485b845a6c0c9b1728e896c8364e51e1b6d67cd2c36dc17ad52409671a8ac8e2376e3bf138869621d03c28841a50cd68bc34fa108",
            "a4010103272006215820eb59d52fbd257d3f8f8f51dd59b2013092763fc9cbc109d32d837920be5e62be",
            "31633364353630312d386563632d343264662d623162302d306132393464306134656435",
            "stake_test1upafv37jqjy8pgrjdauxyxrruqme0hqhh9ryww34mm297agc0f3vc",
            1 // Wrong network mode (should be 0 for testnet address)
        );

        $result = $verifier->verify($request);

        expect($result->isValid)->toBeFalse();
        expect($result->walletMatches)->toBeFalse(); // Wallet won't match with wrong network
        expect($result->payloadMatches)->toBeTrue();
        expect($result->signatureValidates)->toBeTrue();

    });

    test('verification fails when payload challenge does not match', function () {
        $verifier = CIP8Verifier::create();
        $request = new VerificationRequest(
            "84582aa201276761646472657373581de07a9647d2048870a0726f78621863e03797dc17b946473a35ded45f75a166686173686564f4582431633364353630312d386563632d343264662d623162302d3061323934643061346564355840d40e65ebb258bd48d04092f485b845a6c0c9b1728e896c8364e51e1b6d67cd2c36dc17ad52409671a8ac8e2376e3bf138869621d03c28841a50cd68bc34fa108",
            "a4010103272006215820eb59d52fbd257d3f8f8f51dd59b2013092763fc9cbc109d32d837920be5e62be",
            "77726f6e675f6368616c6c656e6765", // Wrong challenge (hex for "wrong_challenge")
            "stake_test1upafv37jqjy8pgrjdauxyxrruqme0hqhh9ryww34mm297agc0f3vc",
            0
        );

        $result = $verifier->verify($request);

        expect($result->isValid)->toBeFalse();
        expect($result->walletMatches)->toBeTrue();
        expect($result->payloadMatches)->toBeFalse(); // Payload should not match
        expect($result->signatureValidates)->toBeTrue();

    });

    test('verification throws exception for invalid CBOR signature', function () {
        $verifier = CIP8Verifier::create();
        $request = new VerificationRequest(
            "ff", // Valid hex but invalid CBOR
            "a4010103272006215820eb59d52fbd257d3f8f8f51dd59b2013092763fc9cbc109d32d837920be5e62be",
            "31633364353630312d386563632d343264662d623162302d306132393464306134656435",
            "stake_test1upafv37jqjy8pgrjdauxyxrruqme0hqhh9ryww34mm297agc0f3vc",
            0
        );

        expect(fn() => $verifier->verify($request))->toThrow(Exception::class);
    });

    test('verification throws exception for invalid signature key', function () {
        $verifier = CIP8Verifier::create();
        $request = new VerificationRequest(
            "84582aa201276761646472657373581de07a9647d2048870a0726f78621863e03797dc17b946473a35ded45f75a166686173686564f4582431633364353630312d386563632d343264662d623162302d3061323934643061346564355840d40e65ebb258bd48d04092f485b845a6c0c9b1728e896c8364e51e1b6d67cd2c36dc17ad52409671a8ac8e2376e3bf138869621d03c28841a50cd68bc34fa108",
            "aa", // Valid hex but invalid signature key
            "31633364353630312d386563632d343264662d623162302d306132393464306134656435",
            "stake_test1upafv37jqjy8pgrjdauxyxrruqme0hqhh9ryww34mm297agc0f3vc",
            0
        );

        expect(fn() => $verifier->verify($request))->toThrow(Exception::class);
    });

    test('verification with modified signature fails signature validation', function () {
        // Create a modified signature by changing a few bytes
        $originalSignatureCbor = "84582aa201276761646472657373581de07a9647d2048870a0726f78621863e03797dc17b946473a35ded45f75a166686173686564f4582431633364353630312d386563632d343264662d623162302d3061323934643061346564355840d40e65ebb258bd48d04092f485b845a6c0c9b1728e896c8364e51e1b6d67cd2c36dc17ad52409671a8ac8e2376e3bf138869621d03c28841a50cd68bc34fa108";
        
        // Modify the last few characters to corrupt the signature
        $modifiedSignatureCbor = substr($originalSignatureCbor, 0, -4) . "0000";

        $verifier = CIP8Verifier::create();
        $request = new VerificationRequest(
            $modifiedSignatureCbor,
            "a4010103272006215820eb59d52fbd257d3f8f8f51dd59b2013092763fc9cbc109d32d837920be5e62be",
            "31633364353630312d386563632d343264662d623162302d306132393464306134656435",
            "stake_test1upafv37jqjy8pgrjdauxyxrruqme0hqhh9ryww34mm297agc0f3vc",
            0
        );

        $result = $verifier->verify($request);

        expect($result->isValid)->toBeFalse();
        expect($result->walletMatches)->toBeTrue(); // Wallet should still match
        expect($result->payloadMatches)->toBeTrue(); // Payload should still match
        expect($result->signatureValidates)->toBeFalse(); // Signature should fail

    });

    test('verification works correctly across multiple calls', function () {
        $verifier = CIP8Verifier::create();
        
        $validRequest = new VerificationRequest(
            "84582aa201276761646472657373581de07a9647d2048870a0726f78621863e03797dc17b946473a35ded45f75a166686173686564f4582431633364353630312d386563632d343264662d623162302d3061323934643061346564355840d40e65ebb258bd48d04092f485b845a6c0c9b1728e896c8364e51e1b6d67cd2c36dc17ad52409671a8ac8e2376e3bf138869621d03c28841a50cd68bc34fa108",
            "a4010103272006215820eb59d52fbd257d3f8f8f51dd59b2013092763fc9cbc109d32d837920be5e62be",
            "31633364353630312d386563632d343264662d623162302d306132393464306134656435",
            "stake_test1upafv37jqjy8pgrjdauxyxrruqme0hqhh9ryww34mm297agc0f3vc",
            0
        );

        // Verify multiple times to ensure consistency
        $result1 = $verifier->verify($validRequest);
        $result2 = $verifier->verify($validRequest);
        $result3 = $verifier->verify($validRequest);

        expect($result1->isValid)->toBeTrue();
        expect($result2->isValid)->toBeTrue();
        expect($result3->isValid)->toBeTrue();
        
        expect($result1->toArray())->toBe($result2->toArray());
        expect($result2->toArray())->toBe($result3->toArray());
    });
});
