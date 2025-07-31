<?php

use CardanoPhp\CIP8Verifier\Service\SignatureVerifier;
use CardanoPhp\CIP8Verifier\DTO\CoseSign1;
use CardanoPhp\CIP8Verifier\Exception\InvalidPublicKeyLengthException;
use CardanoPhp\CIP8Verifier\Exception\InvalidSignatureLengthException;

describe('SignatureVerifier', function () {
    test('can be instantiated', function () {
        $verifier = new SignatureVerifier();
        expect($verifier)->toBeInstanceOf(SignatureVerifier::class);
    });

    test('verifySignature validates correct signature with valid demo data', function () {
        // Use demo data that we know is valid
        $signatureCbor = "84582aa201276761646472657373581de07a9647d2048870a0726f78621863e03797dc17b946473a35ded45f75a166686173686564f4582431633364353630312d386563632d343264662d623162302d3061323934643061346564355840d40e65ebb258bd48d04092f485b845a6c0c9b1728e896c8364e51e1b6d67cd2c36dc17ad52409671a8ac8e2376e3bf138869621d03c28841a50cd68bc34fa108";
        $signatureKey = "a4010103272006215820eb59d52fbd257d3f8f8f51dd59b2013092763fc9cbc109d32d837920be5e62be";
        
        // Parse the COSE data and extract public key
        $parser = new \CardanoPhp\CIP8Verifier\Service\CoseParser();
        $coseData = $parser->parseCoseSign1(hex2bin($signatureCbor));
        
        $extractor = new \CardanoPhp\CIP8Verifier\Service\PublicKeyExtractor();
        $publicKey = $extractor->extractFromSignatureKey($signatureKey);
        
        $verifier = new SignatureVerifier();
        $isValid = $verifier->verifySignature($coseData, $publicKey);
        
        expect($isValid)->toBeTrue();
    });

    test('verifyPayload validates correct payload with demo data', function () {
        $signatureCbor = "84582aa201276761646472657373581de07a9647d2048870a0726f78621863e03797dc17b946473a35ded45f75a166686173686564f4582431633364353630312d386563632d343264662d623162302d3061323934643061346564355840d40e65ebb258bd48d04092f485b845a6c0c9b1728e896c8364e51e1b6d67cd2c36dc17ad52409671a8ac8e2376e3bf138869621d03c28841a50cd68bc34fa108";
        $walletAuthChallengeHex = "31633364353630312d386563632d343264662d623162302d306132393464306134656435";
        
        $parser = new \CardanoPhp\CIP8Verifier\Service\CoseParser();
        $coseData = $parser->parseCoseSign1(hex2bin($signatureCbor));
        
        $verifier = new SignatureVerifier();
        $isValid = $verifier->verifyPayload($coseData, $walletAuthChallengeHex);
        
        expect($isValid)->toBeTrue();
    });

    test('verifyPayload returns false for wrong payload', function () {
        $signatureCbor = "84582aa201276761646472657373581de07a9647d2048870a0726f78621863e03797dc17b946473a35ded45f75a166686173686564f4582431633364353630312d386563632d343264662d623162302d3061323934643061346564355840d40e65ebb258bd48d04092f485b845a6c0c9b1728e896c8364e51e1b6d67cd2c36dc17ad52409671a8ac8e2376e3bf138869621d03c28841a50cd68bc34fa108";
        $wrongPayload = "77726f6e675f7061796c6f6164"; // hex for "wrong_payload"
        
        $parser = new \CardanoPhp\CIP8Verifier\Service\CoseParser();
        $coseData = $parser->parseCoseSign1(hex2bin($signatureCbor));
        
        $verifier = new SignatureVerifier();
        $isValid = $verifier->verifyPayload($coseData, $wrongPayload);
        
        expect($isValid)->toBeFalse();
    });

    test('verifySignature throws InvalidSignatureLengthException for wrong signature length', function () {
        $invalidSignature = str_repeat('a', 32); // Wrong length (should be 64)
        $validPublicKey = str_repeat('b', 32); // Correct length
        
        $coseData = new CoseSign1('protected', null, 'payload', $invalidSignature);
        
        $verifier = new SignatureVerifier();
        
        expect(function () use ($verifier, $coseData, $validPublicKey) {
            $verifier->verifySignature($coseData, $validPublicKey);
        })->toThrow(InvalidSignatureLengthException::class);
    });

    test('verifySignature throws InvalidPublicKeyLengthException for wrong public key length', function () {
        $validSignature = str_repeat('a', 64); // Correct length
        $invalidPublicKey = str_repeat('b', 16); // Wrong length (should be 32)
        
        $coseData = new CoseSign1('protected', null, 'payload', $validSignature);
        
        $verifier = new SignatureVerifier();
        
        expect(function () use ($verifier, $coseData, $invalidPublicKey) {
            $verifier->verifySignature($coseData, $invalidPublicKey);
        })->toThrow(InvalidPublicKeyLengthException::class);
    });

    test('verifySignature returns false for invalid signature with correct lengths', function () {
        $invalidSignature = str_repeat('a', 64); // Correct length but invalid signature
        $publicKey = str_repeat('b', 32); // Correct length
        
        $coseData = new CoseSign1('protected', null, 'payload', $invalidSignature);
        
        $verifier = new SignatureVerifier();
        $result = $verifier->verifySignature($coseData, $publicKey);
        
        expect($result)->toBeFalse();
    });

    test('verifyPayload handles Blake2b hashed payload correctly', function () {
        // Create a test case where the payload in COSE is a Blake2b hash of the challenge
        $originalChallenge = "test_challenge_data";
        $originalChallengeHex = bin2hex($originalChallenge);
        
        // Create Blake2b hash of the challenge (28 bytes)
        $hashedPayload = \CardanoPhp\CIP8Verifier\Utility\Blake2bHasher::hash(hex2bin($originalChallengeHex), 28);
        
        $coseData = new CoseSign1('protected', null, $hashedPayload, 'signature');
        
        $verifier = new SignatureVerifier();
        $result = $verifier->verifyPayload($coseData, $originalChallengeHex);
        
        expect($result)->toBeTrue();
    });

    test('verifyPayload handles direct payload match correctly', function () {
        $challenge = "test_challenge_data";
        $challengeHex = bin2hex($challenge);
        
        // Payload is the direct challenge (not hashed)
        $coseData = new CoseSign1('protected', null, $challenge, 'signature');
        
        $verifier = new SignatureVerifier();
        $result = $verifier->verifyPayload($coseData, $challengeHex);
        
        expect($result)->toBeTrue();
    });
});
