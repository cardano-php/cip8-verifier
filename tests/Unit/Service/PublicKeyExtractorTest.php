<?php

use CardanoPhp\CIP8Verifier\Service\PublicKeyExtractor;

describe('PublicKeyExtractor', function () {
    test('can be instantiated', function () {
        $extractor = new PublicKeyExtractor();
        expect($extractor)->toBeInstanceOf(PublicKeyExtractor::class);
    });

    test('extractFromSignatureKey extracts public key from valid signature key', function () {
        // Use the demo signature key from demo.php
        $signatureKey = "a4010103272006215820eb59d52fbd257d3f8f8f51dd59b2013092763fc9cbc109d32d837920be5e62be";
        
        $extractor = new PublicKeyExtractor();
        $publicKey = $extractor->extractFromSignatureKey($signatureKey);
        
        expect($publicKey)->toBeString();
        expect(strlen($publicKey))->toBe(32); // Ed25519 public key is 32 bytes
    });

    test('extractFromSignatureKey returns consistent results', function () {
        $signatureKey = "a4010103272006215820eb59d52fbd257d3f8f8f51dd59b2013092763fc9cbc109d32d837920be5e62be";
        
        $extractor = new PublicKeyExtractor();
        $publicKey1 = $extractor->extractFromSignatureKey($signatureKey);
        $publicKey2 = $extractor->extractFromSignatureKey($signatureKey);
        
        expect($publicKey1)->toBe($publicKey2);
    });

    test('extractFromSignatureKey handles different signature keys', function () {
        // Test with demo signature key
        $signatureKey1 = "a4010103272006215820eb59d52fbd257d3f8f8f51dd59b2013092763fc9cbc109d32d837920be5e62be";
        
        // Create a different signature key (modify last few bytes)
        $signatureKey2 = "a4010103272006215820eb59d52fbd257d3f8f8f51dd59b2013092763fc9cbc109d32d837920be5e62bf";
        
        $extractor = new PublicKeyExtractor();
        $publicKey1 = $extractor->extractFromSignatureKey($signatureKey1);
        $publicKey2 = $extractor->extractFromSignatureKey($signatureKey2);
        
        expect($publicKey1)->not->toBe($publicKey2);
        expect(strlen($publicKey1))->toBe(32);
        expect(strlen($publicKey2))->toBe(32);
    });

    test('extractFromSignatureKey throws exception for invalid CBOR', function () {
        $invalidSignatureKey = "ff"; // Valid hex but invalid CBOR structure
        
        $extractor = new PublicKeyExtractor();
        
        $threwException = false;
        try {
            $extractor->extractFromSignatureKey($invalidSignatureKey);
        } catch (Throwable $e) {
            $threwException = true;
        }
        
        expect($threwException)->toBeTrue();
    });

    test('extractFromSignatureKey throws exception for malformed signature key', function () {
        $malformedSignatureKey = "deadbeef"; // Valid hex but not a proper COSE key structure
        
        $extractor = new PublicKeyExtractor();
        
        $threwException = false;
        try {
            $extractor->extractFromSignatureKey($malformedSignatureKey);
        } catch (Throwable $e) {
            $threwException = true;
        }
        
        expect($threwException)->toBeTrue();
    });
});