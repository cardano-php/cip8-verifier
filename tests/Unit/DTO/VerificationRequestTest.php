<?php

use CardanoPhp\CIP8Verifier\DTO\VerificationRequest;

describe('VerificationRequest', function () {
    test('can be instantiated with all required parameters', function () {
        $request = new VerificationRequest(
            'test_signature_cbor',
            'test_signature_key',
            'test_wallet_auth_challenge_hex',
            'test_stake_key_address',
            1
        );

        expect($request->signatureCbor)->toBe('test_signature_cbor');
        expect($request->signatureKey)->toBe('test_signature_key');
        expect($request->walletAuthChallengeHex)->toBe('test_wallet_auth_challenge_hex');
        expect($request->stakeKeyAddress)->toBe('test_stake_key_address');
        expect($request->networkMode)->toBe(1);
    });

    test('can be created from array with fromArray method', function () {
        $data = [
            'signatureCbor' => 'test_signature_cbor',
            'signatureKey' => 'test_signature_key',
            'walletAuthChallengeHex' => 'test_wallet_auth_challenge_hex',
            'stakeKeyAddress' => 'test_stake_key_address',
            'networkMode' => 0
        ];

        $request = VerificationRequest::fromArray($data);

        expect($request->signatureCbor)->toBe('test_signature_cbor');
        expect($request->signatureKey)->toBe('test_signature_key');
        expect($request->walletAuthChallengeHex)->toBe('test_wallet_auth_challenge_hex');
        expect($request->stakeKeyAddress)->toBe('test_stake_key_address');
        expect($request->networkMode)->toBe(0);
    });

    test('fromArray works with real demo data', function () {
        $data = [
            'signatureCbor' => "84582aa201276761646472657373581de07a9647d2048870a0726f78621863e03797dc17b946473a35ded45f75a166686173686564f4582431633364353630312d386563632d343264662d623162302d3061323934643061346564355840d40e65ebb258bd48d04092f485b845a6c0c9b1728e896c8364e51e1b6d67cd2c36dc17ad52409671a8ac8e2376e3bf138869621d03c28841a50cd68bc34fa108",
            'signatureKey' => "a4010103272006215820eb59d52fbd257d3f8f8f51dd59b2013092763fc9cbc109d32d837920be5e62be",
            'walletAuthChallengeHex' => "31633364353630312d386563632d343264662d623162302d306132393464306134656435",
            'stakeKeyAddress' => "stake_test1upafv37jqjy8pgrjdauxyxrruqme0hqhh9ryww34mm297agc0f3vc",
            'networkMode' => 0
        ];

        $request = VerificationRequest::fromArray($data);

        expect($request->signatureCbor)->toBe($data['signatureCbor']);
        expect($request->signatureKey)->toBe($data['signatureKey']);
        expect($request->walletAuthChallengeHex)->toBe($data['walletAuthChallengeHex']);
        expect($request->stakeKeyAddress)->toBe($data['stakeKeyAddress']);
        expect($request->networkMode)->toBe($data['networkMode']);
    });
});
